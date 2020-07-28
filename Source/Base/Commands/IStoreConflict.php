<?php
namespace Snuggle\Base\Commands;


interface IStoreConflict
{
	/**
	 * @return IStoreConflict|static
	 */
	public function ignoreConflict(): IStoreConflict;
	
	/**
	 * @return IStoreConflict|static
	 */
	public function overrideConflict(): IStoreConflict;
	
	/**
	 * @return IStoreConflict|static
	 */
	public function failOnConflict(): IStoreConflict;
	
	/**
	 * Merge only the new values from conflicting document.
	 * @return IStoreConflict|static
	 */
	public function mergeNewOnConflict(): IStoreConflict;
	
	/**
	 * Override any existing values with new values.
	 * @return IStoreConflict|static
	 */
	public function mergeOverOnConflict(): IStoreConflict;

	/**
	 * @param callable $callback Callback in format [(?Doc $existing, Doc $new): ?Doc]
	 * @return IStoreConflict|static
	 */
	public function resolveConflict(callable $callback): IStoreConflict;
	
	/**
	 * Update the stored document even if after resolution the new item is identical to the stored one. 
	 * @param bool $force
	 * @return IStoreConflict
	 */
	public function forceResolveUnmodified(bool $force = true): IStoreConflict;
}