<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\Connection\Response\IRawResponse;


interface IExecute 
{
	public function execute(): IRawResponse;
	public function executeSafe(?\Exception &$e = null): ?IRawResponse;
}