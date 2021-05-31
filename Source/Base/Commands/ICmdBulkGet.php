<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;
use Snuggle\Core\StaleBehavior;
use Snuggle\Core\Lists\ViewRow;
use Snuggle\Core\Lists\ViewList;

use Structura\Map;


interface ICmdBulkGet extends IExecute, IQuery, IReadOperation
{
	public function from(string $db, ?string $design = null, ?string $view = null): ICmdBulkGet;
	public function view(string $design, string $view): ICmdBulkGet;
	
	public function includeConflicts(bool $include = true): ICmdBulkGet;
	public function includeDocs(bool $include = true): ICmdBulkGet;
	public function stale(?string $behavior = StaleBehavior::OK): ICmdBulkGet;
	
	public function key($key): ICmdBulkGet;
	public function keys(?array $keys): ICmdBulkGet;
	public function startKey($endKey): ICmdBulkGet;
	public function endKey($endKey): ICmdBulkGet;
	
	public function group($value): ICmdBulkGet;
	
	public function inclusiveEndKey(bool $isInclusive = true): ICmdBulkGet;
	
	public function updateSeq(bool $seq = true): ICmdBulkGet;
	
	public function limit(?int $limit = 100): ICmdBulkGet;
	public function skip(?int $skip = 100): ICmdBulkGet;
	public function page(int $page, int $perPage = 100): ICmdBulkGet;
	public function groupLevel(?int $level): ICmdBulkGet;
	public function descending(bool $isDesc = true): ICmdBulkGet;
	
	public function queryList(): ViewList;
	public function queryValues(): array;
	
	/**
	 * @param mixed $default
	 * @return mixed
	 */
	public function queryValue($default = null);
	
	/**
	 * @return bool
	 */
	public function queryExists(): bool;
	
	
	/**
	 * @return ViewRow[]
	 */
	public function queryRows(): array;
	
	/**
	 * @return ViewRow[]|Map
	 */
	public function queryRowsByKey(): Map;
	
	/**
	 * @return ViewRow[][]|Map
	 */
	public function queryRowsGroupByKey(): Map;
	
	/**
	 * @return ViewRow[]|Map
	 */
	public function queryRowsByDocID(): Map;
	
	/**
	 * @return ViewRow[][]|Map
	 */
	public function queryRowsGroupByDocID(): Map;
	
	/**
	 * @return ViewRow|null
	 */
	public function queryFirstRow(): ?ViewRow;
	
	/**
	 * @return string[]|Map
	 */
	public function queryRevisions(): Map;
	
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array;
	
	/**
	 * @return Doc[]|Map
	 */
	public function queryDocsMap(): Map;
	
	/**
	 * @param string $field
	 * @return Doc[]|Map
	 */
	public function queryDocsMapBy(string $field): Map;
	
	/**
	 * @param string $field
	 * @return Doc[][]|Map
	 */
	public function queryDocsGroupBy(string $field): Map;
	
	/**
	 * @return Doc|null
	 */
	public function queryFirstDoc(): ?Doc;
}