<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;
use Snuggle\Connection\Parsers\SingleDocParser;


abstract class DocStoreResolver extends BaseStoreResolver
{
	protected function doResolve(): void
	{
		$store = $this->getStore();
		$docs = $this->getStoredDocuments();
		
		foreach ($store->Pending as $index => $item)
		{
			/** @var Doc $doc */
			if (!$docs->tryGet($item['_id'], $doc))
			{
				$store->removePendingAt($index);
				continue;
			}
			
			$existingData = $doc->Data;
			$result = $this->resolveDocs(SingleDocParser::parseData($item), $doc);
			
			if (!$result)
			{
				$store->removePendingAt($index);
				continue;
			}
			else if (
				!$this->isForceUpdateUnmodified() &&
				$result->isDataEqualsTo($existingData))
			{
				$store->removePendingAt($index);
				continue;
			}
			
			$store->addConflict($index, $doc);
			
			$result->Rev = $doc->Rev;
			$store->Pending[$index] = $result->toData();
		}
	}
	
	
	protected abstract function resolveDocs(Doc $new, Doc $current): ?Doc;
}