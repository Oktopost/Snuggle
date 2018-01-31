<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\ICommand;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IExecutable extends ICommand 
{
	public function execute(): IRawResponse;
}