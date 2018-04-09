<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;


abstract class BaseStoreResolver implements IBulkStoreResolution
{
	private $from;
	
	/** @var IConnection */
	private $connection;
	
	/** @var IBulkStoreResult */
	private $store;
	
	/** @var IConnector */
	private $connector;
	
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	protected function getStore(): IBulkStoreResult
	{
		return $this->store;
	}
	
	protected function getConnector(): IConnector
	{
		return $this->connector;
	}
	
	protected function db(): string 
	{
		return $this->from;
	}
	
	protected function getPendingIds(): array
	{
		return array_column($this->getStore()->Pending, '_id');
	}
	
	
	protected abstract function doResolve(): void;
	
	
	public function setConnection(IConnector $connector, IConnection $connection): void
	{
		$this->connector = $connector;
		$this->connection = $connection;
	}
	
	public function from(string $db): void
	{
		$this->from = $db;
	}
	
	public function setStore(IBulkStoreResult $store): void
	{
		$this->store = $store;
	}
	
	public function resolve(ConflictException $exception, IRawResponse $response): bool
	{
		$store = $this->getStore();
		
		foreach ($store->Pending as $index => $data)
		{
			if (!key_exists('_id', $data))
				$store->removePendingAt($index);
		}
		
		if ($store->hasPending())
		{
			$this->doResolve();
			return $store->hasPending();
		}
		else
		{
			return false;
		}
	}
}