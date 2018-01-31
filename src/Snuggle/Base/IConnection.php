<?php
namespace Snuggle\Base;


use Snuggle\Base\Request\IRawRequest;
use Snuggle\Base\Response\IRawResponse;


interface IConnection
{
	public function request(IRawRequest $request): IRawResponse; 
}