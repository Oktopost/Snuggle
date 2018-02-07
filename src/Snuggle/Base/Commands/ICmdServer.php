<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Server\Index;


interface ICmdServer
{
	public function info(): Index;
}