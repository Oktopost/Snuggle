<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;


interface ICmdBulkStore extends IStoreConflict
{
	public function setCostumeResolver(IBulkStoreResolution $resolver): ICmdBulkStore;
	
	/**
	 * @param string $db
	 * @return ICmdBulkStore|static
	 */
	public function into(string $db): ICmdBulkStore;
	
	/**
	 * @param array|string $id Document ID or the document itself.
	 * @param array|string|null $rev Document revision, or the document itself.
	 * @param array|null $data Document to store. If set, $id must be string.
	 * @return ICmdBulkStore|static
	 */
	public function data($id, $rev = null, ?array $data = null): ICmdBulkStore;
	
	public function dataSet(array $data, bool $isAssoc = null): ICmdBulkStore;
	
	public function setMaxRetries(?int $maxRetries = null): ICmdBulkStore;
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdBulkStore|static
	 */
	public function asBatch($isAsBatch = true): ICmdBulkStore;
	
	public function execute(?int $maxRetries = null): IBulkStoreResult;
	public function executeSafe(\Exception &$e = null, ?int $maxRetries = null): IBulkStoreResult;
}