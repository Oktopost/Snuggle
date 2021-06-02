<?php
namespace Snuggle\Connection\Decorators;


use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;


class SnuggleCallbackDecorator implements IConnectionDecorator
{
	private $callback;
	private ?IConnection $child = null;
	
	
	/**
	 * @param IRawRequest|string $request
	 * @param string $method
	 * @param array $params
	 * @return IRawResponse
	 */
	public function request($request, string $method = '', array $params = []): IRawResponse
	{
		$callback = $this->callback;
		
		return $callback($this->child, $request, $method, $params);
	}

	public function setChild(IConnection $connection): void
	{
		$this->child = $connection;
	}
	
	
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}
}