<?php
namespace Snuggle\Commands\Store;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\Doc;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Connection\Parsers\SingleDocParser;


/**
 * @property array	$Original
 * @property array	$Pending
 * @property array	$Conflicts
 * @property array	$Final
 * @property int	$TotalRequests
 * @property int	$TotalConflicts
 */
class BulkStoreSet extends LiteObject implements IBulkStoreResult
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Original'			=> LiteSetup::createArray(),
			'Pending'			=> LiteSetup::createArray(),
			'Conflicts'			=> LiteSetup::createArray(),
			'Final'				=> LiteSetup::createArray(),
			'TotalRequests'		=> LiteSetup::createInt(),
			'TotalConflicts'	=> LiteSetup::createInt()
		];
	}
	
	
	public function addDocument(array $data): void
	{
		$this->Original[]	= $data;
		$this->Pending[]	= $data;
	}
	
	public function addDocuments(array $set): void
	{
		$set = array_values($set);
		
		$this->Original	= array_merge($this->Original, $set);
		$this->Pending	= array_merge($this->Pending, $set);
	}
	
	public function hasPending(): bool
	{
		return (bool)$this->Pending;
	}
	
	public function removePendingAt(int $index): void
	{
		if (!isset($this->Pending[$index]))
			return;
		
		unset($this->Pending[$index]);
		$this->Final[$index] = null;
	}
	
	/**
	 * @param int $index
	 * @param array|Doc $data
	 */
	public function addConflict(int $index, $data): void
	{
		if (!isset($this->Conflicts[$index]))
			$this->Conflicts[$index] = [];
		
		if (!$data instanceof Doc)
			$data = SingleDocParser::parseData($data);
		
		$this->TotalConflicts++;
		$this->Conflicts[$index][] = $data;
	}
}