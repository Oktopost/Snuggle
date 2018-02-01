<?php
namespace Snuggle\Factories\Commands;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Base\Factories\ICommandFactory;

use Snuggle\Commands\CmdDB;
use Snuggle\Commands\CmdGet;
use Snuggle\Commands\CmdDirect;
use Snuggle\Commands\CmdServer;


class SimpleFactory implements ICommandFactory
{
	public function db(): ICmdDB
	{
		return new CmdDB();
	}
	
	public function direct(): ICmdDirect
	{
		return new CmdDirect();
	}
	
	public function server(): ICmdServer
	{
		return new CmdServer();
	}
	
	public function get(): ICmdGet
	{
		return new CmdGet();
	}
}