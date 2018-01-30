<?php
namespace Snuggle\Response;


use Snuggle\Base\Response\IBody;
use Snuggle\Base\Response\IRawResponse;


class RawResponse implements IRawResponse
{
	private $code;
	private $headers;
	
	/** @var Body|null */
	private $body = null;
	
	
	public function __construct(int $code, array $headers, ?string $body = null)
	{
		$this->code = $code;
		$this->headers = $headers;
		
		if (!is_null($body))
			$this->body = new Body($body);
	}
	
	public function __clone()
	{
		if ($this->body)
			$this->body = clone $this->body;
	}
	
	
	public function hasBody(): bool
	{
		return (bool)$this->body;
	}
	
	public function getBody(): ?IBody
	{
		return $this->body;
	}
	
	public function getRawBody(): string
	{
		return $this->body ? $this->body->getString() : '';
	}
	
	/**
	 * @param bool $asArray
	 * @return mixed
	 */
	public function getJsonBody($asArray = false)
	{
		return $this->body ? $this->body->getJson($asArray) : null;
	}
	
	public function getCode(): int
	{
		return $this->code;
	}
	
	public function isSuccessful(): bool
	{
		return $this->code % 100 == 2;
	}
	
	public function isFailed(): bool
	{
		return $this->code % 100 != 2;
	}
	
	public function isNotFound(): bool
	{
		return $this->code == 404;
	}
	
	public function isServerError(): bool
	{
		return $this->code % 100 == 5;
	}
	
	public function getHeader(string $name): ?string
	{
		return $this->headers[$name] ?? null;
	}
	
	public function hasHeader(string $name): bool
	{
		return isset($this->headers[$name]);
	}
	
	public function getHeaders(): array 
	{
		return $this->headers;
	}
}