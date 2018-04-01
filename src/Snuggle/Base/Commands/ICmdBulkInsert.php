<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;


interface ICmdBulkInsert extends IExecute, IQuery
{
	/**
	 * @param string $db
	 * @return ICmdBulkInsert|static
	 */
	public function into(string $db): ICmdBulkInsert;
	
	/**
	 * @param array|\stdClass
	 * @return ICmdBulkInsert|static
	 */
	public function data($document): ICmdBulkInsert;
	
	/**
	 * @param array $documents []|\stdClass[]
	 * @param bool|null $isAssoc
	 * @return ICmdBulkInsert|static
	 */
	public function dataSet(array $documents, bool $isAssoc = null): ICmdBulkInsert;
	
	/**
	 * @deprecated 
	 * @param array|\stdClass
	 * @return ICmdBulkInsert|static
	 */
	public function document($document): ICmdBulkInsert;
	
	/**
	 * @deprecated 
	 * @param array[]|\stdClass[]
	 * @return ICmdBulkInsert|static
	 */
	public function documents(array $documents): ICmdBulkInsert;
	
	/**
	 * @return string[]
	 */
	public function queryIDs(): array;
	
	/**
	 * @return bool[]
	 */
	public function queryIsSuccessful(): array;
	
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array;
}