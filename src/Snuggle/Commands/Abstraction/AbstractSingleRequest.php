<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Base\IConnection;
use Snuggle\Connection\Request\RawRequest;


abstract class AbstractSingleRequest extends AbstractExecutable
{
	/** @var RawRequest */
	private $request;
	
	
	protected function request(): RawRequest
	{
		return $this->request;
	}
	
	protected function executeCurrentRequest(): IRawResponse
	{
		return $this->getConnection()->request($this->request);
	}
	
	
	public function __construct(IConnection $connection)
	{
		parent::__construct($connection);
		$this->request = new RawRequest();
	}
	
	
	public function execute(): IRawResponse
	{
		return $this->getConnection()->request($this->request);
	}
}