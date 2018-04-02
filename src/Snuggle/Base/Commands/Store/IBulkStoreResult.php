<?php
namespace Snuggle\Base\Commands\Store;


use Snuggle\Core\Doc;


/**
 * @property array	$Original
 * @property array	$Pending
 * @property array	$Conflicts
 * @property array	$Final
 * @property int	$TotalRequests
 * @property int	$TotalConflicts
 */
interface IBulkStoreResult
{
	public function hasPending(): bool;
	public function removePendingAt(int $index): void;
	
	/**
	 * @param int $index
	 * @param array|Doc $data
	 */
	public function addConflict(int $index, $data): void;
}