<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdBulkStore;
use Snuggle\Base\Commands\IStoreConflict;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Store\BulkStoreSet;
use Snuggle\Conflict\Resolvers\BulkStore;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\HttpException;


class CmdBulkStore implements ICmdBulkStore
{
	private $db;
	private $retires = null;
	
	/** @var IConnection */
	private $connection;
	
	/** @var IBulkStoreResult */
	private $resolver;
	
	/** @var BulkStoreSet */
	private $data;
	
	
	private function executeRequest(ConflictException $e = null): IRawResponse
	{
		if (!$this->db)
			throw new FatalSnuggleException('Database name must be set!');
		
		$request = RawRequest::create("/{$this->db}/_bulk_docs", Method::POST);
		$request->setBody(array_values($this->data->Pending));
		
		try
		{
			return $this->connection->request($request);
		}
		catch (ConflictException $e)
		{
			return $e->getResponse();
		}
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection	= $connection;
		$this->data			= new BulkStoreSet();
	}
	
	
	/**
	 * @param string $db
	 * @return ICmdBulkStore|static
	 */
	public function into(string $db): ICmdBulkStore
	{
		$this->db = $db;
		return $this;
	}
	
	public function setCostumeResolver(IBulkStoreResolution $resolver): ICmdBulkStore
	{
		$resolver->setConnection($this->connection);
		$this->resolver = $resolver;
		return $this;
	}
	
	/**
	 * @return IStoreConflict|static
	 */
	public function ignoreConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStore\IgnoreResolver());
	}
	
	/**
	 * @return IStoreConflict|static
	 */
	public function overrideConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStore\OverrideResolver());
	}
	
	/**
	 * @return IStoreConflict|static
	 */
	public function failOnConflict(): IStoreConflict
	{
		// TODO: Implement failOnConflict() method.
	}
	
	/**
	 * Merge only the new values from conflicting document.
	 * @return IStoreConflict|static
	 */
	public function mergeNewOnConflict(): IStoreConflict
	{
		// TODO: Implement mergeNewOnConflict() method.
	}
	
	/**
	 * Override any existing values with new values.
	 * @return IStoreConflict|static
	 */
	public function mergeOverOnConflict(): IStoreConflict
	{
		// TODO: Implement mergeOverOnConflict() method.
	}
	
	/**
	 * @param callable $callback Callback in format [(?Doc $existing, Doc $new): ?Doc]
	 * @return IStoreConflict|static
	 */
	public function resolveConflict(callable $callback): IStoreConflict
	{
		// TODO: Implement resolveConflict() method.
	}
	
	/**
	 * @param array|string $id Document ID or the document itself.
	 * @param array|string|null $rev Document revision, or the document itself.
	 * @param array|null $data Document to store. If set, $id must be string.
	 * @return ICmdBulkStore|static
	 */
	public function data($id, $rev = null, ?array $data = null): ICmdBulkStore
	{
		// TODO: Implement data() method.
	}
	
	public function dataSet(array $data, bool $isAssoc = null): ICmdBulkStore
	{
		// TODO: Implement dataSet() method.
	}
	
	public function setMaxRetries(?int $maxRetries = null): ICmdBulkStore
	{
		$this->retires = $maxRetries;
		return $this;
	}
	
	public function execute(?int $maxRetries = null): IBulkStoreResult
	{
		// TODO: Implement execute() method.
	}
	
	public function executeSafe(\Exception &$e = null, ?int $maxRetries = null): IBulkStoreResult
	{
		try
		{
			return $this->execute($maxRetries);
		}
		catch (HttpException $he)
		{
			$e = $he;
			// TODO: Parse: $he->
		}
		catch (\Throwable $t)
		{
			$e = $t;
			// TODO
		}
	}
}