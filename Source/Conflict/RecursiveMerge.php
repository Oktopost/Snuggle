<?php
namespace Snuggle\Conflict;


use Traitor\TStaticClass;


class RecursiveMerge
{
	use TStaticClass;
	
	
	public static function mergeNew(array $a, array ...$b): array
	{
		foreach ($b as $arr)
		{
			foreach ($arr as $key => $value)
			{
				if (key_exists($key, $a))
				{
					if (is_array($value) && !key_exists(0, $value) && 
						is_array($a[$key]) && !key_exists(0, $a[$key]))
					{
						$a[$key] = RecursiveMerge::mergeNew($a[$key], $arr[$key]);
					}
				}
				else
				{
					$a[$key] = $value;
				}
			}
		}
		
		return $a;
	}
	
	public static function merge(array $a, array ...$b): array
	{
		foreach ($b as $arr)
		{
			foreach ($arr as $key => $value)
			{
				if (key_exists($key, $a))
				{
					if (is_array($value) && !key_exists(0, $value))
					{
						$a[$key] = RecursiveMerge::merge($a[$key], $arr[$key]);
					}
					else
					{
						$a[$key] = $value;
					}
				}
				else
				{
					$a[$key] = $value;
				}
			}
		}
		
		return $a;
	}
}