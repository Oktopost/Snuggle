<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IExecutable;


abstract class AbstractExecutable extends AbstractCommand implements IExecutable
{
	use TExecuteSafe;
}