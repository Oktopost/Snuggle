<?php
namespace Snuggle\Base\Conflict\Commands;


use Snuggle\Base\Conflict\Commands\Generic\IGetDocConflictCommand;
use Snuggle\Base\Conflict\Commands\Generic\ISetDocConflictCommand;


interface IStoreConflictCommand extends IGetDocConflictCommand, ISetDocConflictCommand
{
	
}