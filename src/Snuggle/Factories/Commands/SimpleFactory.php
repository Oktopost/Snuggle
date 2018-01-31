<?php
namespace Snuggle\Factories\Commands;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Factories\ICommandFactory;


class SimpleFactory implements ICommandFactory
{
	public function db(): ICmdDB
	{
		return null;
	}
	
	public function direct(): ICmdDirect
	{
		return null;
	}
}