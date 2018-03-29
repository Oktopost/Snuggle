<?php
namespace Snuggle\Base\Conflict\Commands\Generic;


interface ISetDocConflictCommand extends IGetRevConflictCommand
{
	public function getRev(): ?string;
	public function getBody(): array;
	public function setBody(array $body): void;
}