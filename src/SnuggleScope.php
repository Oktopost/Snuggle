<?php
namespace Snuggle;


use Skeleton\Skeleton;
use Traitor\TStaticClass;


class SnuggleScope
{
	use TStaticClass;
	
	
	/** @var Skeleton */
	private static $skeleton = null;
	
	
	/**
	 * @param null|string $interface
	 * @return Skeleton|mixed
	 */
	public static function skeleton(?string $interface = null)
	{
		if (!self::$skeleton)
		{
			self::$skeleton = new Skeleton();
			self::$skeleton->useGlobal();
		}
		
		return $interface ? self::$skeleton->get($interface) : self::$skeleton;
	}
}