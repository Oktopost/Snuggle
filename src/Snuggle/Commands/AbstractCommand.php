<?php
namespace Snuggle\Commands;


use Snuggle\Base\ICommand;
use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;


class AbstractCommand implements ICommand
{
	/** @var IConnection */
	private $connection;
	
	
	protected function requestURI($uri, $method = Method::GET): IRawResponse
	{
		$request = new RawRequest();
		$request
			->setURI($uri)
			->setMethod($method);
		
		return $this->connection->request($request);
	}
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	
	public function __clone()
	{
		
	}
	
	public function setConnection(IConnection $connection): void
	{
		$this->connection = $connection;
	}
}