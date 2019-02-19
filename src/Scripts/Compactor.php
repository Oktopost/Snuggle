<?php
namespace Snuggle\Scripts;


use Snuggle\CouchDB;


class Compactor
{
	private $connectionSet = false;
	private $filters = ['*'];
	
	/** @var CouchDB */
	private $couchDB;
	
	
	public function __construct()
	{
		$this->couchDB = new CouchDB();
	}
	
	
	public function setConnection(array $config): Compactor
	{
		$this->connectionSet = true;
		$this->couchDB->config()->addConnection('main', $config);
		return $this;
	}
	
	
	public function setDBFilters($filters): Compactor
	{
		$this->filters = $filters;
		return $this;
	}
	
	
	public function run(int $maxQueries = 1): void
	{
		
	}
}