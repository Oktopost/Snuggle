<?php
namespace Snuggle\Commands\Conflict;


use Snuggle\Base\Connection\Response\IRawResponse;


interface IResolutionStrategy
{
	public function execute(): IRawResponse;
}