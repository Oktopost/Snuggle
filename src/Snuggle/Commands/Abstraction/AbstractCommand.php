<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\ICommand;
use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;


class AbstractCommand implements ICommand
{
	/** @var IConnection */
	private $connection;
	
	
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
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
}