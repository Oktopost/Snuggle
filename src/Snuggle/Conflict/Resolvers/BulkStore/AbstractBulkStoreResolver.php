<?php
namespace Snuggle\Conflict\Resolvers\BulkStore;


use Snuggle\Base\IConnection;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;


class AbstractBulkStoreResolver implements IBulkStoreResolution
{
	/** @var IConnection */
	private $connection;
	
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	
	public function setConnection(IConnection $connection): void
	{
		$this->connection = $connection;
	}
	
	public function setStore(IBulkStoreResolution $store): void
	{
		
	}
	
	public function resolve(?ConflictException $exception, IRawResponse $response): bool
	{
		// TODO: Implement resolve() method.
	}
}