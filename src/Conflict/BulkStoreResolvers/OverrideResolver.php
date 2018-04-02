<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


class OverrideResolver extends BaseStoreResolver
{
	public function doResolve(): void
	{
		$store = $this->getStore();
		
		$revisions = $this->getConnector()->getAll()
			->from($this->db())
			->keys($this->getPendingIds())
			->queryRevisions();
		
		foreach ($store->Pending as $index => $data)
		{
			if (!$revisions->tryGet($data['_id'], $rev))
			{
				$store->removePendingAt($index);
				continue;
			}
			
			$store->addConflict($index, ['_id' => $data['_id'], '_rev' => $rev]);
			$store->Pending[$index]['_rev'] = $rev;
		}
	}
}