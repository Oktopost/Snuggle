<?php
namespace Snuggle\Response;


use Snuggle\Base\Response\IBody;
use Snuggle\Exceptions\UnexpectedResponseException;


class Body implements IBody
{
	/** @var string */
	private $body;
	
	/** @var mixed */
	private $decodedAsArrayBody = null;
	
	
	public function __construct(string $body)
	{
		$this->body = $body;
	}
	
	public function __toString()
	{
		return $this->body;
	}
	
	
	public function isEmpty(): bool
	{
		return (bool)$this->body;
	}
	
	public function length(): int
	{
		return strlen($this->body);
	}
	
	public function getString(): string
	{
		return $this->body;
	}
	
	public function getJson($asArray = false) 
	{
		if ($asArray && $this->decodedAsArrayBody)
			return $this->decodedAsArrayBody;
		
		$result = json_decode($this->body, $asArray);
		
		if (is_null($result) && strtolower(trim($this->body)) != 'null')
			throw new UnexpectedResponseException('Failed to parse body as json response.');
		
		if ($asArray)
			$this->decodedAsArrayBody = $result;
		
		return $result;
	}
}