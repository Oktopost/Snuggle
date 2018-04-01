<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;
use Snuggle\Core\StaleBehavior;
use Snuggle\Core\Lists\AllDocsList;

use Structura\Map;


interface ICmdBulkGet extends IExecute, IQuery
{
	public function from(string $db): ICmdBulkGet;
	
	public function includeConflicts(bool $include = true): ICmdBulkGet;
	public function includeDocs(bool $include = true): ICmdBulkGet;
	public function stale(?string $behavior = StaleBehavior::OK): ICmdBulkGet;
	
	public function key(?string $key): ICmdBulkGet;
	public function keys(?array $keys): ICmdBulkGet;
	public function startKey(?string $endKey): ICmdBulkGet;
	public function endKey(?string $endKey): ICmdBulkGet;
	
	public function inclusiveEndKey(bool $isInclusive = true): ICmdBulkGet;
	
	public function updateSeq(bool $seq = true): ICmdBulkGet;
	
	public function limit(?int $limit = 100): ICmdBulkGet;
	public function skip(?int $skip = 100): ICmdBulkGet;
	public function page(int $page, int $perPage = 100): ICmdBulkGet;
	public function descending(bool $isDesc = true): ICmdBulkGet;
	
	public function queryList(): AllDocsList;
	
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array;
	
	/**
	 * @return Doc[]|Map
	 */
	public function queryMap(): Map;
	
	/**
	 * @param string $field
	 * @return Doc[]|Map
	 */
	public function queryMapBy(string $field): Map;
	
	/**
	 * @param string $field
	 * @return Doc[][]|Map
	 */
	public function queryGroupBy(string $field): Map;
}