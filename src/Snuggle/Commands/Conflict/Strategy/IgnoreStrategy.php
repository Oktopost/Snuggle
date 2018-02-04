<?php
namespace Snuggle\Commands\Conflict\Strategy;


use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;


class IgnoreStrategy extends AbstractStrategy
{
	/** @var IRawRequest */
	private $request;
	
	
	public function setRequest(IRawRequest $request): IgnoreStrategy
	{
		$this->request = $request;
		return $this;
	}
	
	public function execute(): IRawResponse
	{
		try
		{
			return $this->conn()->request($this->request);
		}
		catch (ConflictException $e)
		{
			return $e->getResponse();
		}
	}
}