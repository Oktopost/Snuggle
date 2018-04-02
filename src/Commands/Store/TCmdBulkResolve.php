<?php
namespace Snuggle\Commands\Store;


use Snuggle\Base\Commands\IStoreConflict;
use Snuggle\Conflict\BulkStoreResolvers;


trait TCmdBulkResolve
{
	/**
	 * @return IStoreConflict|static
	 */
	public function ignoreConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\IgnoreResolver());
	}
	
	/**
	 * @return IStoreConflict|static
	 */
	public function overrideConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\OverrideResolver());
	}
	
	/**
	 * @return IStoreConflict|static
	 */
	public function failOnConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\FailResolver());
	}
	
	/**
	 * Merge only the new values from conflicting document.
	 * @return IStoreConflict|static
	 */
	public function mergeNewOnConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\MergeNewResolver());
	}
	
	/**
	 * Override any existing values with new values.
	 * @return IStoreConflict|static
	 */
	public function mergeOverOnConflict(): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\MergeOverResolver());
	}
	
	/**
	 * @param callable $callback Callback in format [(?Doc $existing, Doc $new): ?Doc]
	 * @return IStoreConflict|static
	 */
	public function resolveConflict(callable $callback): IStoreConflict
	{
		return $this->setCostumeResolver(new BulkStoreResolvers\CallbackResolver($callback));
	}
}