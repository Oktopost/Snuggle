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
	
	
	public function __construct(IRawResponse $response, IRawRequest $request, ?string $message = null)
	{
		parent::__construct($message);
		
		$this->response = $response;
		$this->request = $request;
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