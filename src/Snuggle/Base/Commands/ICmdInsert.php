<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;

interface ICmdInsert extends IExecutable, IQuery
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
	public function data($data, $value = null): ICmdInsert;
	
	/**
	 * Return the ETag of the inserted document.
	 * @return string
	 */
	public function queryETag(): string;
	
	public function queryDoc(): Doc;
}