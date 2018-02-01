<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\IExecutable;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\HttpException;
use Snuggle\Connection\Request\RawRequest;


class AbstractExecutable extends AbstractCommand implements IExecutable
{
	/** @var RawRequest */
	private $request;
	
	
	protected function request(): RawRequest
	{
		return $this->request;
	}
	
	protected function executeRequest(): IRawResponse
	{
		return $this->getConnection()->request($this->request);
	}
	
	
	public function __construct()
	{
		$this->request = new RawRequest();
	}
	
	
	public function execute(): IRawResponse
	{
		return $this->getConnection()->request($this->request);
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