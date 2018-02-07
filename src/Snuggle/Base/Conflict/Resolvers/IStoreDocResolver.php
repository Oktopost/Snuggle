<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\Conflict\Commands\IStoreConflictCommand;
use Snuggle\Base\Conflict\Resolvers\Generic\IMergeResolver;
use Snuggle\Base\Conflict\Resolvers\Generic\ISimpleResolver;
use Snuggle\Base\Conflict\Resolvers\Generic\ICallbackResolver;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IStoreDocResolver extends ISimpleResolver, IMergeResolver, ICallbackResolver
{
	/**
	 * @param callable $callback Callback in format [(Doc $existing, Doc $data): ?Doc]
	 */
	public function resolveConflict(callable $callback): void;
	
	public function execute(IStoreConflictCommand $command): IRawResponse;
}