<?php
namespace Snuggle\Scripts\Compact;


use Structura\Strings;
use Traitor\TStaticClass;


class DBNameFilter
{
	use TStaticClass;
	
	
	private static function sortFilters(array $filters): array
	{
		$match = [];
		$negate = [];
		
		foreach ($filters as $filter)
		{
			if ($filter[0] == '!')
			{
				$negate[] = Strings::shouldNotStartWith($filter, '!');
			}
			else
			{
				$match[] = $filter;
			}
		}
		
		return [$match, $negate];
	}
	
	private static function isMatching(string $name, array $match, array $negate): bool
	{
		$isMatching = false;
		
		if ($match)
		{
			foreach ($match as $m)
			{
				if (fnmatch($m, $name))
				{
					$isMatching = true;
					break;
				}
			}
		}
		else
		{
			$isMatching = true;
		}
		
		if (!$isMatching)
			return false;
		
		foreach ($negate as $n)
		{
			if (fnmatch($n, $name))
			{
				return false;
			}
		}
		
		return true;
	}
	
	
	public static function filter(array $names, array $filters): array
	{
		if (!$filters) return $names;
		
		list($match, $negate) = self::sortFilters($filters);
		
		$matching = [];
		
		foreach ($names as $name)
		{
			if (self::isMatching($name, $match, $negate))
			{
				$matching[] = $name;
			}
		}
		
		return $matching;
	}
}