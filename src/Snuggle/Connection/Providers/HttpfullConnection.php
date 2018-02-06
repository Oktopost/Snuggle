<?php
namespace Snuggle\Connection\Providers;


use Httpful\Request;
use Httpful\Response;
use Httpful\Exception\ConnectionErrorException;

use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Config\ConnectionConfig;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Response\RawResponse;

use Snuggle\Exceptions\SnuggleException;
use Snuggle\Exceptions\ServerUnreachableException;


class HttpfullConnection implements IConnection
{
	/** @var ConnectionConfig */
	private $config;
	
	
	private function send(IRawRequest $request): Response
	{
		$host = $this->config->getURL();
		$uri = $request->getURI();
		
		if ($uri)
		{
			if ($host[strlen($host) -1] != '/')
				$host .= '/';
			
			if ($uri[0] == '/')
				$uri = substr($uri, 1);
		}
		
		$url = $host . $uri;
		
		if ($request->hasQueryParams())
		{
			$url .= '?' . http_build_query($request->getQueryParams());
		}
		
		$httpfullRequest = Request::init()
			->withoutAutoParsing()
			->uri($url)
			->method($request->getMethod())
			->addHeaders($request->getHeaders());
		
		if ($request->hasBody())
		{
			$httpfullRequest->body($request->getBody());
		}
		
		return $httpfullRequest->send();
	}
	
	private function parse(IRawRequest $request, Response $response): IRawResponse
	{
		return new RawResponse(
			$request,
			$response->code, 
			$response->headers->toArray(),
			$response->hasBody() ? $response->body : null
		);
	}
	
	
	public function __construct(ConnectionConfig $config)
	{
		$this->config = $config;
	}
	
	/**
	 * @param IRawRequest|string $request
	 * @param string $method
	 * @param array $params
	 * @return IRawResponse
	 */
	public function request($request, string $method = '', array $params = []): IRawResponse
	{
		$request = RawRequest::toRequest($request, $method, $params);
		
		try
		{
			$response = $this->send($request);
		}
		catch (ConnectionErrorException $httpfullException)
		{
			throw new ServerUnreachableException($httpfullException->getMessage(), $httpfullException->getCode());
		}
		catch (\Exception $e)
		{
			throw new SnuggleException($e->getMessage(), $e->getCode());
		}
		
		return $this->parse($request, $response);
	}
}