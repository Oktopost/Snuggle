<?php
namespace Snuggle\Base\Conflict\Commands\Generic;


use Snuggle\Base\Commands\IAssemble;
use Snuggle\Base\Commands\IRevCommand;


interface IGetRevConflictCommand extends IAssemble, IRevCommand
{
	public function getDB(): string;
	public function getDocID(): string;
	public function getReadQuorum(): ?int;
}