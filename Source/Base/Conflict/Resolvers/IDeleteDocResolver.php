<?php
namespace Snuggle\Base\Conflict\Resolvers;


use Snuggle\Base\Conflict\Commands\IDeleteConflictCommand;
use Snuggle\Base\Conflict\Resolvers\Generic\ISimpleResolver;
use Snuggle\Base\Conflict\Resolvers\Generic\ICallbackResolver;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IDeleteDocResolver extends ISimpleResolver, ICallbackResolver
{
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 */
	public function resolveConflict(callable $callback): void;
	
	public function execute(IDeleteConflictCommand $command): IRawResponse;
}