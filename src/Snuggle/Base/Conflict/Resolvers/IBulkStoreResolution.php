<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;


interface IBulkStoreResolution
{
	public function setConnection(IConnection $connection): void;
	public function setStore(IBulkStoreResult $store): void;
	public function resolve(ConflictException $exception, IRawResponse $response): bool;
}