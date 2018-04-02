<?php
namespace Snuggle\Connection\Parsers\Document;


use Snuggle\Core\Document\RevisionInfo;
use Traitor\TStaticClass;
use Structura\Map;


class RevisionInfoParser
{
	use TStaticClass;
	
	
	public static function parse(array $data): ?RevisionInfo
	{
		if (!isset($data['rev']) || 
			!is_string($data['rev']) || 
			!isset($data['status']) || 
			!is_string($data['status']))
		{
			return null;
		}
		
		return new RevisionInfo($data['rev'], $data['status']);
	}
	
	/**
	 * @param array $data
	 * @return Map|RevisionInfo[]
	 */
	public static function parseAll(array $data): Map
	{
		$res = new Map();
		
		foreach ($data as $item)
		{
			if (!is_array($item))
				continue;
			
			$rev = self::parse($item);
			
			if (!$rev) 
				continue;
			
			$res[$rev->Rev] = $rev;
		}
		
		return $res;
	}
}