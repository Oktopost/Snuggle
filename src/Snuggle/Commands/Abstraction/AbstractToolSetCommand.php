<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\AbstractCommand;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;


class AbstractToolSetCommand extends AbstractCommand
{
	protected function executeRequest($uri, $method = Method::GET, array $params = []): IRawResponse
	{
		$request = $this->createRequest($uri, $method, $params);
		return $this->getConnection()->request($request);
	}
	
	protected function createRequest($uri, $method = Method::GET, array $params = []): RawRequest
	{
		$request = new RawRequest();
		
		return $request
			->setURI($uri)
			->setQueryParams($params)
			->setMethod($method);
	}
}