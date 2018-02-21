<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;


class CmdDirect implements ICmdDirect
{
	use TQuery;
	use TExecuteSafe;
	
	
	/** @var RawRequest */
	private $request;
	
	/** @var IConnection */
	private $connection;
	
	
	private function setData(string $method, ?string $uri = null, array $params = [], string $body = null): CmdDirect
	{
		if ($uri)
			$this->setURI($uri);
		
		if ($params)
			$this->setQueryParams($params);
		
		if ($body)
			$this->setBody($body);
		
		return $this->setMethod($method);
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
		$this->request = new RawRequest();
	}
	
	
	public function setBody($body): ICmdDirect
	{
		$this->request->setBody($body);
		return $this;
	}
	
	public function setHeader(string $name, string $value): ICmdDirect
	{
		$this->request->setHeader($name, $value);
		return $this;
	}
	
	public function setHeaders(array $headers): ICmdDirect
	{
		$this->request->setHeaders($headers);
		return $this;
	}
	
	public function setURI(string $uri): ICmdDirect
	{
		$this->request->setURI($uri);
		return $this;
	}
	
	public function setQueryParam(string $param, $value): ICmdDirect
	{
		$this->request->setQueryParam($param, $value);
		return $this;
	}
	
	public function setJsonQueryParam(string $param, $value): ICmdDirect
	{
		$this->request->setJsonQueryParam($param, $value);
		return $this;
	}
	
	public function setQueryParams(array $params): ICmdDirect
	{
		$this->request->setQueryParams($params);
		return $this;
	}
	
	public function setJsonQueryParams(array $params): ICmdDirect
	{
		$this->request->setJsonQueryParams($params);
		return $this;
	}
	
	public function setMethod(string $method): ICmdDirect
	{
		$this->request->setMethod($method);
		return $this;
	}
	
	public function setGET(?string $uri = null, array $params = []): ICmdDirect
	{
		return $this->setData(Method::GET, $uri, $params);
	}
	
	public function setHEAD(?string $uri = null, array $params = []): ICmdDirect
	{
		return $this->setData(Method::HEAD, $uri, $params);
	}
	
	public function setPUT(?string $uri = null, array $params = [], string $body = null): ICmdDirect
	{
		return $this->setData(Method::PUT, $uri, $params, $body);
	}
	
	public function setPOST(?string $uri = null, array $params = [], string $body = null): ICmdDirect
	{
		return $this->setData(Method::POST, $uri, $params, $body);
	}
	
	public function setDELETE(?string $uri = null, array $params = []): ICmdDirect
	{
		return $this->setData(Method::DELETE, $uri, $params);
	}
	
	public function execute(): IRawResponse
	{
		return $this->connection->request($this->request);
	}
}