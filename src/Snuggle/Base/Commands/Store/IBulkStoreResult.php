<?php
namespace Snuggle\Base\Commands\Store;


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
}