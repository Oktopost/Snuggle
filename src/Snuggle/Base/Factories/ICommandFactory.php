<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Commands\ICmdServer;


interface ICommandFactory
{
	public function db(IConnection $connection): ICmdDB; 
	public function get(IConnection $connection): ICmdGet;
	public function insert(IConnection $connection): ICmdInsert;
	public function delete(IConnection $connection): ICmdDelete;
	public function server(IConnection $connection): ICmdServer; 
	public function direct(IConnection $connection): ICmdDirect;
}