<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;


abstract class BaseStoreResolver implements IBulkStoreResolution
{
	/** @var IConnection */
	private $connection;
	
	/** @var IBulkStoreResult */
	private $store;
	
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	protected function getStore(): IBulkStoreResult
	{
		return $this->store;
	}
	
	
	public function setConnection(IConnection $connection): void
	{
		$this->connection = $connection;
	}
	
	public function setStore(IBulkStoreResult $store): void
	{
		$this->store = $store;
	}
}