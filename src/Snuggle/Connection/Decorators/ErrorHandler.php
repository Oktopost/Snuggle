<?php
namespace Snuggle\Connection\Decorators;


use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\SnuggleException;
use Snuggle\Exceptions\HttpExceptionFactory;


class ErrorHandler extends AbstractDecorator implements IConnectionDecorator
{
	public function request(IRawRequest $request): IRawResponse
	{
		try
		{
			$response = $this->invokeChild($request);
		}
		catch (SnuggleException $snuggleException)
		{
			throw $snuggleException;
		}
		catch (\Exception $e)
		{
			throw new SnuggleException($e->getMessage(), $e->getCode(), $e);
		}
		
		if ($response->isFailed())
		{
			throw HttpExceptionFactory::getException($response);
		}
		
		return $response;
	}
}