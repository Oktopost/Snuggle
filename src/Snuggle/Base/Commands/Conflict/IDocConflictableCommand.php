<?php
namespace Snuggle\Base\Commands\Conflict;


use Snuggle\Base\Commands\IDocCommand;
use Snuggle\Base\Connection\Request\IRawRequest;


interface IDocConflictableCommand extends IDocCommand 
{
	public function getDocId(): string;
	public function getDB(): string;
	public function assemble(): IRawRequest;
}