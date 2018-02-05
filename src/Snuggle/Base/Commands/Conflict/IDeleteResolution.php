<?php
namespace Snuggle\Base\Commands\Conflict;


interface IDeleteResolution extends IDocResolution
{
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 */
	public function resolveConflict(callable $callback): void;
}