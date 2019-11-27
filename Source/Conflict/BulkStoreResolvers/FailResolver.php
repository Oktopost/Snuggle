<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Base\Conflict\Resolvers\AbstractBulkStoreResolver;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;


class FailResolver extends AbstractBulkStoreResolver
{
	public function resolve(ConflictException $exception, IRawResponse $response): bool
	{
		throw $exception;
	}
}