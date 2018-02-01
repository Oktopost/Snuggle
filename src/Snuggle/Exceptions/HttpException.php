<?php
namespace Snuggle\Exceptions;


use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;


class HttpException extends SnuggleException
{
	/** @var IRawResponse */
	private $response;
	
	/** @var IRawRequest */
	private $request;
	
	
	private function createMessage(IRawResponse $response, ?string $message = null): string
	{
		if ($message)
			$message .= '. ';
		else 
			$message = '';
		
		$body = $response->getRawBody() ?: '';
		
		return $message . "Code {$response->getCode()} body {$body}";
	}
	
	
	public function __construct(IRawResponse $response, ?string $message = null)
	{
		parent::__construct($this->createMessage($response, $message));
		
		$this->response = $response;
		$this->request = $response->request();
	}
	
	
	public function getResponse(): IRawResponse
	{
		return $this->response;
	}
	
	public function getRequest(): IRawRequest
	{
		return $this->request;
	}
}