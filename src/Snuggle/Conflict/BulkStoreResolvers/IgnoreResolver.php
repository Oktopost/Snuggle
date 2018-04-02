<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Base\Conflict\Resolvers\AbstractBulkStoreResolver;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;


class IgnoreResolver extends AbstractBulkStoreResolver
{
	public function resolve(ConflictException $exception, IRawResponse $response): bool
	{
		return false;
	}
}