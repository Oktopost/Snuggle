<?php
namespace Snuggle\Commands\Conflict;


use Snuggle\Base\Connection\Response\IRawResponse;


interface IDocConflictResolutionConnection
{
	public function setStrategy(string $strategy): void;
	public function execute(IDocConflictableCommand $command): IRawResponse;
}