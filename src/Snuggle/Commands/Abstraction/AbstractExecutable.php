<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IExecutable;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Connection\Method;
use Snuggle\Exceptions\HttpException;
use Snuggle\Connection\Request\RawRequest;


abstract class AbstractExecutable extends AbstractCommand implements IExecutable
{
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