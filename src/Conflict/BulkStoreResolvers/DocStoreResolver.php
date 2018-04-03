<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;
use Snuggle\Connection\Parsers\SingleDocParser;


abstract class DocStoreResolver extends BaseStoreResolver
{
	protected function doResolve(): void
	{
		$store = $this->getStore();
		
		$docs = $this->getConnector()->getAll()
			->from($this->db())
			->keys($this->getPendingIds())
			->includeDocs()
			->limit()
			->queryDocsMap();
		
		foreach ($store->Pending as $index => $item)
		{
			if (!$docs->tryGet($item['_id'], $doc))
			{
				$store->removePendingAt($item);
				continue;
			}
			
			/** @var Doc $doc */
			$store->addConflict($index, $doc);
			$result = $this->resolveDocs(SingleDocParser::parseData($item), $doc);
			
			if (!$result)
			{
				$store->removePendingAt($item);
			}
			else
			{
				$result->Rev = $doc->Rev;
				$store->Pending[$index] = $result->toData();
			}
		}
	}
	
	
	protected abstract function resolveDocs(Doc $new, Doc $current): ?Doc;
}