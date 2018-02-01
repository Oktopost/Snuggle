<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdServer;


interface ICommandFactory
{
	public function db(): ICmdDB; 
	public function get(): ICmdGet;
	public function server(): ICmdServer; 
	public function direct(): ICmdDirect;
}