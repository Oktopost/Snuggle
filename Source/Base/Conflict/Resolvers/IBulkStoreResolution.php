<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;


interface IBulkStoreResolution
{
	public function setReadQuorum(int $read): void;
	public function forceUpdateUnmodified(bool $force = false): void;
	public function setConnection(IConnector $connector, IConnection $connection): void;
	public function setStore(IBulkStoreResult $store): void;
	public function from(string $db): void;
	public function resolve(ConflictException $exception, IRawResponse $response): bool;
}