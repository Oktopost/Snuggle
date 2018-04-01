<?php
namespace Snuggle\Commands\Store;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Base\Commands\Store\IBulkStoreResult;


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
	
	
	public function hasPending(): bool
	{
		return (bool)$this->Pending;
	}
}