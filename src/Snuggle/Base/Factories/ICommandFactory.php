<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Base\IConnection;


interface ICommandFactory
{
	public function db(IConnection $connection): ICmdDB; 
	public function get(IConnection $connection): ICmdGet;
	public function delete(IConnection $connection): ICmdDelete;
	public function server(IConnection $connection): ICmdServer; 
	public function direct(IConnection $connection): ICmdDirect;
}