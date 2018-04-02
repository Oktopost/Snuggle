<?php
namespace Snuggle\Commands\Store;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;
use Traitor\TStaticClass;


class ResponseParser
{
	use TStaticClass;
	
	
	public static function parse(BulkStoreSet $set, IRawResponse $response): void
	{
		$body			= $response->getJsonBody();
		$pendingObjects = array_values($set->Pending);
		$pendingIndex	= array_keys($set->Pending);
		$newPending		= [];
		$conflicts		= 0;
		
		for ($i = 0; $i < count($body); $i++)
		{
			$result = $body[$i];
			
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
				$conflicts++;
			}
		}
		
		$set->Pending = $newPending;
		$set->TotalConflicts += $conflicts;
		
		if ($conflicts)
		{
			throw new ConflictException($response);
		}
	}
}