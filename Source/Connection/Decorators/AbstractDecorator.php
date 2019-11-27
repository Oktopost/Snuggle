<?php
namespace Snuggle\Connection\Decorators;


use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\Response\IRawResponse;


abstract class AbstractDecorator implements IConnectionDecorator
{
	/** @var IConnection */
	private $child = null;
	
	
	protected function child(): IConnection
	{
		return $this->child;
	}
	
	protected function invokeChild($request, string $method = '', array $params = []): IRawResponse
	{
		return $this->child->request($request, $method, $params);
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