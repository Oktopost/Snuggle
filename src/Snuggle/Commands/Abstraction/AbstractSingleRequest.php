<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Connection\Request\RawRequest;


abstract class AbstractSingleRequest extends AbstractExecutable
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
}