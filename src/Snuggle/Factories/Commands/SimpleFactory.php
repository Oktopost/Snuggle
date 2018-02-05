<?php
namespace Snuggle\Factories\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Base\Factories\ICommandFactory;

use Snuggle\Commands\CmdDB;
use Snuggle\Commands\CmdGet;
use Snuggle\Commands\CmdDelete;
use Snuggle\Commands\CmdDirect;
use Snuggle\Commands\CmdServer;
use Snuggle\Commands\Conflict\Templates\DeleteResolution;


class SimpleFactory implements ICommandFactory
{
	public function db(IConnection $connection): ICmdDB
	{
		return new CmdDB($connection);
	}
	
	public function direct(IConnection $connection): ICmdDirect
	{
		return new CmdDirect($connection);
	}
	
	public function server(IConnection $connection): ICmdServer
	{
		return new CmdServer($connection);
	}
	
	public function get(IConnection $connection): ICmdGet
	{
		return new CmdGet($connection);
	}
	
	public function delete(IConnection $connection): ICmdDelete
	{
		return new CmdDelete(new DeleteResolution($connection));
	}
}