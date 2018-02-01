<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Commands\Common\TQuery;


class CmdDirect extends AbstractExecutable implements ICmdDirect
{
	use TQuery;
	
	
	public function setBody($body): ICmdDirect
	{
		$this->request()->setBody($body);
		return $this;
	}
	
	public function setHeader(string $name, string $value): ICmdDirect
	{
		$this->request()->setHeader($name, $value);
		return $this;
	}
	
	public function setHeaders(array $headers): ICmdDirect
	{
		$this->request()->setHeaders($headers);
		return $this;
	}
	
	public function setURI(string $uri): ICmdDirect
	{
		$this->request()->setURI($uri);
		return $this;
	}
	
	public function setQueryParam(string $param, $value): ICmdDirect
	{
		$this->request()->setQueryParam($param, $value);
		return $this;
	}
	
	public function setJsonQueryParam(string $param, $value): ICmdDirect
	{
		$this->request()->setJsonQueryParam($param, $value);
		return $this;
	}
	
	public function setQueryParams(array $params): ICmdDirect
	{
		$this->request()->setQueryParams($params);
		return $this;
	}
	
	public function setJsonQueryParams(array $params): ICmdDirect
	{
		$this->request()->setJsonQueryParams($params);
		return $this;
	}
	
	public function setMethod(string $method): ICmdDirect
	{
		$this->request()->setMethod($method);
		return $this;
	}
}