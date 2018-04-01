<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\Commands\ICmdBulkGet;
use Snuggle\Base\IConnection;

use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Commands\ICmdServer;

use Snuggle\Base\Commands\ICmdBulkInsert;


interface ICommandFactory
{
	public function db(IConnection $connection): ICmdDB; 
	public function get(IConnection $connection): ICmdGet;
	public function store(IConnection $connection): ICmdStore;
	public function insert(IConnection $connection): ICmdInsert;
	public function delete(IConnection $connection): ICmdDelete;
	public function server(IConnection $connection): ICmdServer; 
	public function direct(IConnection $connection): ICmdDirect;
	
	public function getAll(IConnection $connection): ICmdBulkGet;
	public function insertAll(IConnection $connection): ICmdBulkInsert;
}