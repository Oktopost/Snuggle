<?php
namespace Snuggle\Base\Commands;


interface ICmdDelete extends IExecutable, IQuery, ISingleDoc
{
	/**
	 * @param bool $isAsBatch
	 * @return ICmdDelete|static
	 */
	public function asBatch(bool $isAsBatch = true): ICmdDelete;
	
	/**
	 * @return ICmdDelete|static
	 */
	public function ignoreConflict(): ICmdDelete;
	
	/**
	 * @return ICmdDelete|static
	 */
	public function overrideConflict(): ICmdDelete;
	
	/**
	 * @return ICmdDelete|static
	 */
	public function failOnConflict(): ICmdDelete;
	
	/**
	 * @param bool $fail
	 * @return ICmdDelete|static
	 */
	public function failOnNotFound(bool $fail = true): ICmdDelete;
	
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 * @return ICmdDelete|static
	 */
	public function resolveConflict(callable $callback): ICmdDelete;
	
	/**
	 * @param string $strategy See ConflictBehavior enum.
	 * @return ICmdDelete|static
	 */
	public function setConflictBehavior(string $strategy): ICmdDelete;
}