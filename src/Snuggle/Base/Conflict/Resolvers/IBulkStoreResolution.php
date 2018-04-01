<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IBulkStoreResolution
{
	public function setConnection(IConnection $connection): void;
	public function resolve(IBulkStoreResult $result, IRawResponse $response): ?array;
}