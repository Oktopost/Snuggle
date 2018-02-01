<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IExecutable;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Connection\Method;
use Snuggle\Exceptions\HttpException;
use Snuggle\Connection\Request\RawRequest;


abstract class AbstractExecutable extends AbstractCommand implements IExecutable
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
	
	
	public function executeSafe(?\Exception &$e = null): ?IRawResponse
	{
		try
		{
			return $this->execute();
		}
		catch (HttpException $httpException)
		{
			$e = $httpException;
			return $e->getResponse();
		}
		catch (\Exception $thrown)
		{
			$e = $thrown;
			return null;
		}
	}
}