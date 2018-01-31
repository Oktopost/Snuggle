<?php
namespace Snuggle\Base;


use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IConnection
{
	public function request(IRawRequest $request): IRawResponse; 
}