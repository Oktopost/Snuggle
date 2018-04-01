<?php
namespace Snuggle\Commands\Store;


use Traitor\TStaticClass;


class ResponseParser
{
	use TStaticClass;
	
	
	public static function parse(BulkStoreSet $set, array $response): void
	{
		$pendingObjects = array_values($set->Pending);
		$pendingIndex	= array_keys($set->Pending);
		$newPending		= [];
		
		for ($i = 0; $i < count($response); $i++)
		{
			$result = $response[$i];
			
			$originIndex	= $pendingIndex[$i];
			$object			= $pendingObjects[$i];
			
			if ($result['ok'] ?? false)
			{
				$object['_id']	= $result['id'];
				$object['_rev']	= $result['rev'];
				
				$set->Final[$originIndex] = $object;
			}
			else
			{
				$newPending[$originIndex] = $object;
			}
		}
		
		$set->Pending = $newPending;
	}
}