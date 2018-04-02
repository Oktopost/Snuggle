<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;


abstract class AbstractBulkStoreResolver implements IBulkStoreResolution
{
	public function setConnection(IConnection $connection): void {}
	public function setStore(IBulkStoreResult $store): void {}
}