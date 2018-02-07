<?php
namespace Snuggle\Base\Conflict\Commands\Generic;


use Snuggle\Base\Commands\IAssemble;
use Snuggle\Base\Commands\IDocCommand;


interface IGetDocConflictCommand extends IAssemble, IDocCommand
{
	public function getDB(): string;
	public function getDocID(): string;
}