<?php
namespace Snuggle\Base;


use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;


interface IConnection
{
	/**
	 * @param IRawRequest|string $request
	 * @param string $method
	 * @param array $params
	 * @return IRawResponse
	 */
	public function request($request, string $method = '', array $params = []): IRawResponse;
}