<?php
namespace Snuggle\Connection\Decorators;


use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;


abstract class AbstractDecorator implements IConnectionDecorator
{
	/** @var IConnection */
	private $child = null;
	
	
	protected function child(): IConnection
	{
		return $this->child;
	}
	
	protected function invokeChild(IRawRequest $request): IRawResponse
	{
		return $this->child->request($request);
	}
	
	
	public function __construct(?IConnection $connection = null)
	{
		if ($connection)
			$this->child = $connection;
	}
	
	
	public function setChild(IConnection $connection): void
	{
		$this->child = $connection;
	}
}