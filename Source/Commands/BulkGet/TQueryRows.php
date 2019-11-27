<?php
namespace Snuggle\Commands\BulkGet;


use Snuggle\Core\Lists\ViewRow;
use Snuggle\Exceptions\SnuggleException;
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
		$body	= $this->queryJson();
		$values	= [];
		
		$rows = $body['rows'] ?? []; 
		
		foreach ($rows as $row)
		{
			if (key_exists('value', $row))
				$values[] = $row['value'];
		}
		
		return $values;
	}
	
	public function queryValue($default = null)
	{
		$body	= $this->queryJson();
		$row	= ($body['rows'][0] ?? []);
		
		if (func_num_args() == 1)
			return $row['value'] ?? $default;
		else if (!key_exists('value', $row))
			throw new SnuggleException('No value selected. Body (base64) ' . base64_encode(jsonencode($body)));
		
		return $row['value'];
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