<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\IStoreConflict;
use Structura\Arrays;

use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdBulkStore;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Store\BulkStoreSet;
use Snuggle\Commands\Store\ResponseParser;
use Snuggle\Commands\Store\TCmdBulkResolve;
use Snuggle\Commands\Abstraction\TRefreshView;

use Snuggle\Connection\Method;
use Snuggle\Exceptions\HttpException;
use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Connection\Request\RawRequest;


class CmdBulkStore implements ICmdBulkStore
{
	use TCmdBulkResolve;
	use TRefreshView;
	
	
	private int $quorumRead = 0;
	private int $quorumWrite = 0;
	
	private $db;
	private $retires = null;
	private $forceUpdateUnmodified = false;
	
	/** @var IConnection */
	private $connection;
	
	/** @var IConnector */
	private $connector;
	
	/** @var IBulkStoreResolution */
	private $resolver;
	
	/** @var BulkStoreSet */
	private $data;
	
	
	private function executeRequest(): IRawResponse
	{
		if (!$this->db)
			throw new FatalSnuggleException('Database name must be set!');
		
		$docs = array_values($this->data->Pending);
		$body = ['docs' => $docs];
		
		$request = RawRequest::create("/{$this->db}/_bulk_docs", Method::POST);
		$request->setBody($body);
		
		if ($this->quorumWrite)
		{
			$request->setQueryParam('w', $this->quorumWrite);
		}
		
		return $this->connection->request($request);
	}
	
	private function getRetries(?int $maxRetries): int
	{
		if (!is_null($maxRetries))
			return max($maxRetries, 0);
		
		if (!is_null($this->retires))
			return max($maxRetries, 0);
		
		return PHP_INT_MAX;
	}
	
	
	public function __construct(IConnector $connector, IConnection $connection)
	{
		$this->connector	= $connector;
		$this->connection	= $connection;
		$this->data			= new BulkStoreSet();
		
		$this->setRefreshConnection($connection);
		$this->ignoreConflict();
	}
	
	
	/**
	 * @param string $db
	 * @return ICmdBulkStore|static
	 */
	public function into(string $db): ICmdBulkStore
	{
		$this->resolver->from($db);
		$this->db = $db;
		$this->setRefreshDB($db);
		return $this;
	}
	
	public function setCostumeResolver(IBulkStoreResolution $resolver): ICmdBulkStore
	{
		$resolver->setConnection($this->connector, $this->connection);
		$resolver->setStore($this->data);
		
		if ($this->quorumRead)
			$resolver->setReadQuorum($this->quorumRead);
		
		if ($this->db)
			$resolver->from($this->db);
				
		$this->resolver = $resolver;
		$this->resolver->forceUpdateUnmodified($this->forceUpdateUnmodified);
		
		return $this;
	}
	
	/**
	 * @param array|string $id Document ID or the document itself.
	 * @param array|string|null $rev Document revision, or the document itself.
	 * @param array|null $data Document to store. If set, $id must be string.
	 * @return ICmdBulkStore|static
	 */
	public function data($id, $rev = null, ?array $data = null): ICmdBulkStore
	{
		if (is_array($id))
			$data = $id;
		else if (is_array($rev))
			$data = $rev;
		else if (!is_array($data))
			throw new FatalSnuggleException('No document provided');
		
		if (is_scalar($id))
			$data['_id'] = (string)$id;
		
		if (is_scalar($rev))
			$data['_rev'] = (string)$rev;
		
		$this->data->addDocument($data);
		
		return $this;
	}
	
	public function dataSet(array $data, bool $isAssoc = false): ICmdBulkStore
	{
		if (is_null($isAssoc))
			$isAssoc = Arrays::isAssoc($data);
		
		if ($isAssoc)
		{
			foreach ($data as $key => $value)
			{
				$value['_id'] = (string)$key;
			}
		}
		
		$this->data->addDocuments($data);
		return $this;
	}
	
	public function setMaxRetries(?int $maxRetries = null): ICmdBulkStore
	{
		$this->retires = $maxRetries;
		return $this;
	}
	
	public function forceUpdateUnmodified(bool $force = true): IStoreConflict
	{
		$this->forceUpdateUnmodified = $force;
		
		if ($this->resolver)
			$this->resolver->forceUpdateUnmodified($this->forceUpdateUnmodified);
		
		return $this;
	}

	/**
	 * @param int $quorum
	 * @return static
	 */
	public function readQuorum(int $quorum)
	{
		$this->quorumRead = $quorum;
		return $this;
	}
	
	/**
	 * @param int $quorum
	 * @return static
	 */
	public function writeQuorum(int $quorum)
	{	
		$this->quorumWrite = $quorum;
		return $this;
	}
	
	public function quorum(int $read, int $write)
	{
		$this->quorumWrite = $write;
		$this->quorumRead = $read;
		
		if ($this->resolver)
			$this->resolver->setReadQuorum($read);
		
		return $this;
	}
	
	
	public function execute(?int $maxRetries = null): IBulkStoreResult
	{
		$retires = $this->getRetries($maxRetries);
		$doRetry = true;
		
		while ($retires-- > 0 && $doRetry)
		{
			$this->data->TotalRequests++;
			
			$response = $this->executeRequest();
			
			try
			{
				ResponseParser::parse($this->data, $response);
				$doRetry = false;
			}
			catch (ConflictException $ce)
			{
				$doRetry = $this->resolver->resolve($ce, $response);
			}
		}
		
		if ($this->data->Final)
		{
			$this->refreshViews(count($this->data->Final));
		}
		
		return $this->data;
	}
	
	public function executeSafe(\Exception &$e = null, ?int $maxRetries = null): ?IBulkStoreResult
	{
		try
		{
			$this->execute($maxRetries);
		}
		catch (HttpException $he)
		{
			$e = $he;
		}
		catch (\Throwable $t)
		{
			$e = $t;
			return null;
		}
		
		return $this->data;
	}
}