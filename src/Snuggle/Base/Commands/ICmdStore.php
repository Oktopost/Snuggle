<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;


interface ICmdStore extends IExecute, IQuery, IQueryRevision, IDocCommand
{
	/**
	 * @param bool $isAsBatch
	 * @return ICmdStore|static
	 */
	public function asBatch($isAsBatch = true): ICmdStore;
	
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
	
	/**
	 * @param array|string|Doc $data
	 * @param mixed|null $value
	 * @return ICmdStore|static
	 */
	public function data($data, $value = null): ICmdStore;
}