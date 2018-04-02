<?php
namespace Snuggle\Base\Conflict\Commands;


use Snuggle\Base\Conflict\Commands\Generic\IGetRevConflictCommand;
use Snuggle\Base\Conflict\Commands\Generic\ISetDocConflictCommand;


interface IStoreConflictCommand extends IGetRevConflictCommand, ISetDocConflictCommand
{
	
}