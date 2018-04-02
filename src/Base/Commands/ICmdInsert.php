<?php
namespace Snuggle\Base\Commands;


interface ICmdInsert extends IExecute, IQuery, IQueryRevision, IDocCommand
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
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function data($data, $value = null): ICmdInsert;
	
	/**
	 * @deprecated 
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function document($data, $value = null): ICmdInsert;
	
	/**
	 * @deprecated 
	 * @param string $id
	 * @return ICmdInsert
	 */
	public function setID(string $id): ICmdInsert;
}