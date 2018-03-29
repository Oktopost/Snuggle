<?php
namespace Snuggle\Base\Commands;


interface ICmdStore extends ICmdInsert, IRevCommand
{
	/**
	 * @return ICmdStore|static
	 */
	public function ignoreConflict(): ICmdStore;
	
	/**
	 * @return ICmdStore|static
	 */
	public function overrideConflict(): ICmdStore;
	
	/**
	 * @return ICmdStore|static
	 */
	public function failOnConflict(): ICmdStore;
	
	/**
	 * Merge only the new values from conflicting document.
	 * @return ICmdStore|static
	 */
	public function mergeNewOnConflict(): ICmdStore;
	
	/**
	 * Override any existing values with new values.
	 * @return ICmdStore|static
	 */
	public function mergeOverOnConflict(): ICmdStore;
	
	/**
	 * @param callable $callback Callback in format [(Doc $existing, Doc $new): ?Doc]
	 * @return ICmdStore|static
	 */
	public function resolveConflict(callable $callback): ICmdStore;
}