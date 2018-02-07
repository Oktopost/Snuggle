<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\Connection\Request\IRawRequest;


interface IAssemble
{
	public function assemble(): IRawRequest;
}