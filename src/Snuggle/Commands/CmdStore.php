<?php
namespace Snuggle\Commands;


use Snuggle\Core\Doc;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\IDocCommand;
use Snuggle\Base\Conflict\Commands\IStoreConflictCommand;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TDocCommand;
use Snuggle\Commands\Abstraction\TExecuteSafe;
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
	
	
	private $data		= [];
	private $rev		= null;
	private $asBatch	= false;
	
	/** @var StoreDocResolver */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = new StoreDocResolver($connection);
		$this->connection->overrideConflict();
	}
	
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdStore|static
	 */
	public function asBatch($isAsBatch = true): ICmdStore
	{
		$this->asBatch = $isAsBatch;
		return $this;
	}
	
	public function ignoreConflict(): ICmdStore
	{
		$this->connection->ignoreConflict();
		return $this;
	}
	
	public function overrideConflict(): ICmdStore
	{
		$this->connection->overrideConflict();
		return $this;
	}
	
	public function failOnConflict(): ICmdStore
	{
		$this->connection->failOnConflict();
		return $this;
	}
	
	public function mergeNewOnConflict(): ICmdStore
	{
		$this->connection->mergeNewOnConflict();
		return $this;
	}
	
	public function mergeOverOnConflict(): ICmdStore
	{
		$this->connection->mergeOverOnConflict();
		return $this;
	}
	
	public function resolveConflict(callable $callback): ICmdStore
	{
		$this->connection->resolveConflict($callback);
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
	 * @return IDocCommand|static
	 */
	public function rev(string $rev): IDocCommand
	{
		$this->rev = $rev;
		return $this;
	}
	
	/**
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdStore|static
	 */
	public function data($data, $value = null): ICmdStore
	{
		if (is_array($data))
		{
			$this->data = $data;
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
		return $this->connection->execute($this);
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
		
		$request = RawRequest::create($uri, $method, $params)
			->setHeader('Content-Type', 'application/json')
			->setBody($this->data);
		
		return $request;
	}
}