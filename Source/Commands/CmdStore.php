<?php
namespace Snuggle\Commands;


use Snuggle\Core\Doc;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Commands\IRevCommand;
use Snuggle\Base\Commands\IStoreConflict;
use Snuggle\Base\Conflict\Commands\IStoreConflictCommand;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TDocCommand;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Commands\Abstraction\TRefreshView;
use Snuggle\Commands\Abstraction\TQueryRevision;

use Snuggle\Conflict\Resolvers\StoreDocResolver;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;


class CmdStore implements ICmdStore, IStoreConflictCommand
{
	use TQuery;
	use TQueryRevision;
	use TDocCommand;
	use TExecuteSafe;
	use TRefreshView;
	
	
	private $data		= [];
	private $rev		= null;
	private $asBatch	= false;
	
	/** @var StoreDocResolver */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = new StoreDocResolver($connection);
		$this->connection->overrideConflict();
		$this->setRefreshConnection($connection);
	}
	
	
	/**
	 * @param string $db
	 * @param string|null $id
	 * @return ICmdInsert|static
	 */
	public function into(string $db, string $id = null): ICmdInsert
	{
		$this->from($db);
		$this->setRefreshDB($db);
		
		if ($id)
			$this->doc($id);
		
		return $this;
	}
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdInsert|static
	 */
	public function asBatch($isAsBatch = true): ICmdInsert
	{
		$this->asBatch = $isAsBatch;
		return $this;
	}
	
	public function ignoreConflict(): IStoreConflict
	{
		$this->connection->ignoreConflict();
		return $this;
	}
	
	public function overrideConflict(): IStoreConflict
	{
		$this->connection->overrideConflict();
		return $this;
	}
	
	public function failOnConflict(): IStoreConflict
	{
		$this->connection->failOnConflict();
		return $this;
	}
	
	public function mergeNewOnConflict(): IStoreConflict
	{
		$this->connection->mergeNewOnConflict();
		return $this;
	}
	
	public function mergeOverOnConflict(): IStoreConflict
	{
		$this->connection->mergeOverOnConflict();
		return $this;
	}
	
	public function resolveConflict(callable $callback): IStoreConflict
	{
		$this->connection->resolveConflict($callback);
		return $this;
	}
	
	public function forceResolveUnmodified(bool $force = true): IStoreConflict
	{
		$this->connection->forceResolveUnmodified($force);
		return $this;
	}
	
	public function getRev(): ?string
	{
		return $this->rev;
	}
	
	public function getBody(): array
	{
		return $this->data;
	}
	
	public function setBody(array $body): void
	{
		$this->data($body);
	}
	
	/**
	 * @param string $rev
	 * @return IRevCommand|static
	 */
	public function rev(string $rev): IRevCommand
	{
		$this->rev = $rev;
		return $this;
	}
	
	/**
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function data($data, $value = null): ICmdInsert
	{
		if (is_array($data))
		{
			$this->data = $data;
			
			if (isset($data['_id']))
				$this->doc($data['_id']);
		}
		else if (is_string($data))
		{
			$this->data[$data] = $value;
		}
		else if ($data instanceof Doc)
		{
			$this->doc($data->ID, $data->Rev);
			$this->data = $data->Data;
		}
		
		return $this;
	}
	
	public function execute(): IRawResponse
	{
		$result = $this->connection->execute($this);
		
		if ($result->isSuccessful())
		{
			$this->refreshViews();
		}
		
		return $result;
	}
	
	public function assemble(): IRawRequest
	{
		$this->requireDBAndDocID();
		
		$uri	= $this->uri();
		$method	= ($this->getDocID() ? Method::PUT : Method::POST);
		$params	= [];
		
		if ($this->asBatch)
			$params['batch'] = 'ok';
				
		if ($this->rev)
			$params['rev']= $this->rev;
		
		$request = RawRequest::create($uri, $method, $params);
		$request->setBody($this->data);
		
		return $request;
	}
	
	
	/**
	 * @deprecated
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function document($data, $value = null): ICmdInsert
	{
		return $this->data($data, $value);
	}
	
	/**
	 * @deprecated 
	 * @param string $id
	 * @return ICmdInsert
	 */
	public function setID(string $id): ICmdInsert
	{
		$this->doc($id);
		return $this;
	}
}