<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;


class OverrideResolver extends BaseStoreResolver
{
	public function doOverrideIfModified(): void
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
	
	private function doOverride(): void
	{
		$store = $this->getStore();
		
		$existing = $this->getConnector()->getAll()
			->from($this->db())
			->keys($this->getPendingIds())
			->queryDocsMap();
		
		foreach ($store->Pending as $index => $data)
		{
			/** @var Doc $doc */
			if (!$existing->tryGet($data['_id'], $doc) ||
				$doc->isDataEqualsTo($data))
			{
				$store->removePendingAt($index);
				continue;
			}
			
			$rev = $doc->Rev;
			
			$store->addConflict($index, ['_id' => $data['_id'], '_rev' => $rev]);
			$store->Pending[$index]['_rev'] = $rev;
		}
	}
	
	
	public function doResolve(): void
	{
		if ($this->isForceUpdateUnmodified())
		{
			$this->doOverrideIfModified();
		}
		else
		{
			$this->doOverride();
		}
	}
}