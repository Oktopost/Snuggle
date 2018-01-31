<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdDirect;


interface ICommandFactory
{
	public function db(): ICmdDB; 
	public function direct(): ICmdDirect;
}