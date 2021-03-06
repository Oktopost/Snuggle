<?php
namespace Snuggle\Connection\Request;


use Objection\Mapper;
use Objection\LiteObject;

use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Connection\Method;
use Snuggle\Exceptions\InvalidBodyException;


class RawRequest implements IRawRequest
{
	private $uri = '';
	private $method = Method::GET;
	
	/** @var string|null */	
	private $body = null;
	
	/** @var string[] */
	private $queryParams = [];
	
	/** @var string[] */
	private $headers = [];
	
	
	public function __construct(?string $uri = null)
	{
		if ($uri)
			$this->setURI($uri);
		
		$this->setHeader('Content-Type', 'application/json');
	}
	
	
	public function setURI(string $uri = ''): RawRequest
	{
		$this->uri = $uri;
		return $this;
	}
	
	public function setQueryParam(string $name, $value): RawRequest
	{
		$this->queryParams[$name] = (string)$value;
		return $this;
	}
	
	public function setJsonQueryParam(string $name, $value): RawRequest
	{
		$this->queryParams[$name] = jsonencode($value);
		return $this;
	}
	
	public function setQueryParams($params): RawRequest
	{
		foreach ($params as $key => $value)
		{
			$this->queryParams[$key] = (string)$value;
		}
		
		return $this;
	}
	
	public function setJsonQueryParams($params): RawRequest
	{
		foreach ($params as $key => $value)
		{
			$this->setJsonQueryParam($key, $value);
		}
		
		return $this;
	}
	
	public function setBody($body): RawRequest
	{
		if (is_null($body) || is_scalar($body))
		{
			$this->body = $body;
		}
		else if (is_array($body) && isset($body[0]) && $body[0] instanceof LiteObject)
		{
			$this->body = Mapper::getJsonFor($body);
		}
		else if (is_array($body) || $body instanceof \stdClass)
		{
			if (!((array)$body))
				$this->body = '{}';
			else
				$this->body = jsonencode($body);
		}
		else if ($body instanceof LiteObject)
		{
			$this->body = Mapper::getJsonFor($body);
		}
		else 
		{
			throw new InvalidBodyException();
		}
		
		return $this;
	}
	
	public function setMethod(string $method): RawRequest
	{
		$this->method = $method;
		return $this;
	}
	
	public function setHeader(string $name, string $value): RawRequest
	{
		$this->headers[$name] = $value;
		return $this;
	}
	
	public function setHeaders(array $values): RawRequest
	{
		$this->headers = array_merge($this->headers, $values);
		return $this;
	}
	
	
	public function getURI(): string
	{
		return $this->uri;
	}
	
	public function getQueryParams(): array
	{
		return $this->queryParams;
	}
	
	public function hasQueryParams(): bool
	{
		return (bool)$this->queryParams;
	}
	
	public function getBody(): ?string
	{
		return $this->body;
	}
	
	public function hasBody(): bool
	{
		return !is_null($this->body);
	}
	
	public function getHeaders(): array
	{
		return $this->headers;
	}
	
	public function getMethod(): string
	{
		return $this->method;
	}
	
	public function setGet(): RawRequest
	{
		$this->method = Method::GET;
		return $this;
	}
	
	public function setPost(): RawRequest
	{
		$this->method = Method::POST;
		return $this;
	}
	
	public function setPut(): RawRequest
	{
		$this->method = Method::PUT;
		return $this;
	}
	
	public function setDelete(): RawRequest
	{
		$this->method = Method::DELETE;
		return $this;
	}
	
	
	/**
	 * @param string|IRawRequest $target
	 * @param string $method
	 * @param array $params
	 * @return IRawRequest
	 */
	public static function toRequest($target, string $method = Method::GET, array $params = []): IRawRequest
	{
		if (is_string($target))
			return self::create($target, $method ?: Method::GET, $params);
		
		return $target;
	}
	
	public static function create(string $uri, string $method = Method::GET, array $params = [])
	{
		$request = new RawRequest();
		
		return $request
			->setURI($uri)
			->setQueryParams($params)
			->setMethod($method);
	}
}