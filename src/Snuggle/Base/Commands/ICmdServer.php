<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\ICommand;
use Snuggle\Core\Server\Index;


interface ICmdServer extends ICommand
{
	public function info(): Index;
}