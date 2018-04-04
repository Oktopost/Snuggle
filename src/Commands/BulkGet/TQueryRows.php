<?php
namespace Snuggle\Commands\BulkGet;


use Snuggle\Core\Lists\ViewRow;
use Structura\Map;


/**
 * @mixin \Snuggle\Commands\CmdBulkGet
 */
trait TQueryRows
{
	/**
	 * @param string $field
	 * @return ViewRow[][]|Map
	 */
	private function _queryRowsGroupBy(string $field): Map
	{
		$list = $this->queryList();
		$map = new Map();
		
		foreach ($list->Rows as $row)
		{
			if (!$map->tryGet($row->$field, $group))
				$group = [];
			
			$group[] = $row;
			$map[$row->$field] = $group;
		}
		
		return $map;
	}
	
	/**
	 * @param string $field
	 * @return ViewRow[]|Map
	 */
	private function _queryRowsByField(string $field): Map
	{
		$list = $this->queryList();
		$map = new Map();
		
		foreach ($list->Rows as $row)
		{
			$map->add($row->$field, $row);
		}
		
		return $map;
	}
	
	
	/**
	 * @return ViewRow|null
	 */
	public function queryFirstRow(): ?ViewRow
	{
		$list = (clone $this)
			->limit(1)
			->queryList();
		
		return $list->Rows ? $list->Rows[0] : null;
	}
	
	public function queryValues(): array
	{
		$rows	= $this->queryList()->Rows;
		$values	= [];
		
		foreach ($rows as $row)
		{
			$values[] = $row->Value;
		}
		
		return $values;
	}
	
	/**
	 * @return ViewRow[]
	 */
	public function queryRows(): array
	{
		return $this->queryList()->Rows;
	}
	
	/**
	 * @return ViewRow[]|Map
	 */
	public function queryRowsByKey(): Map
	{
		return $this->_queryRowsByField('Key');
	}
	
	/**
	 * @return ViewRow[][]|Map
	 */
	public function queryRowsGroupByKey(): Map
	{
		return $this->_queryRowsGroupBy('Key');
	}
	
	/**
	 * @return ViewRow[]|Map
	 */
	public function queryRowsByDocID(): Map
	{
		return $this->_queryRowsByField('DocID');
	}
	
	/**
	 * @return ViewRow[][]|Map
	 */
	public function queryRowsGroupByDocID(): Map
	{
		return $this->_queryRowsGroupBy('DocID');
	}
}