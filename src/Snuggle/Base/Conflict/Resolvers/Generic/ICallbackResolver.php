<?php
namespace Snuggle\Base\Conflict\Resolvers\Generic;


interface ICallbackResolver
{
	public function resolveConflict(callable $callback): void;
}