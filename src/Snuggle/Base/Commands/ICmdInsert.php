<?php
namespace Snuggle\Base\Commands;


interface ICmdInsert extends IExecute, IQuery, IQueryRevision
{
	/**
	 * @param string $db
	 * @param string|null $id
	 * @return ICmdInsert|static
	 */
	public function into(string $db, string $id = null): ICmdInsert;
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdInsert|static
	 */
	public function asBatch($isAsBatch = true): ICmdInsert;
	
	/**
	 * @param string $id
	 * @return ICmdInsert|static
	 */
	public function setID(string $id): ICmdInsert;
	
	/**
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function document($data, $value = null): ICmdInsert;
}