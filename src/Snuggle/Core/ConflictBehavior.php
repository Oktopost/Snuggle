<?php
namespace Snuggle\Core;


use Traitor\TEnum;


class ConflictBehavior
{
	use TEnum;
	
	
	/**
	 * Fully erase previous version and put the new document.
	 */
	public const OVERRIDE	= 'override';
	
	/**
	 * Do nothing on conflict.
	 */
	public const IGNORE		= 'ignore';
	
	/**
	 * Use provided resolution algorithm.
	 */
	public const RESOLVE	= 'resolve';
	
	/**
	 * Merge only new values into existing object.
	 */
	public const MERGE_NEW	= 'merge_new';
	
	/**
	 * Override any exist values, but keep values not present in the new document.
	 */
	public const MERGE_OVER	= 'merge_over'; 
}