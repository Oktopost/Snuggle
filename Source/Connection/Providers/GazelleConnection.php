<?php
namespace Snuggle\Connection\Providers;


use Gazelle\Exceptions\ResponseException;
use Gazelle\Gazelle;
use Gazelle\IResponse;
use Gazelle\Exceptions\RequestException;

use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Connection\Response\RawResponse;
use Snuggle\Exceptions\SnuggleException;
use Snuggle\Exceptions\ServerUnreachableException;
use Snuggle\Connection\Request\RawRequest;


class GazelleConnection implements IConnection
{
	private Gazelle $gazelle;
	
	
	private function send(IRawRequest $request): IResponse
	{
		$gazelleRequest = $this->gazelle->request()
			->addPath($request->getURI())
			->setMethod($request->getMethod())
			->setHeaders($request->getHeaders());
		
		foreach ($request->getQueryParams() as $key => $value)
		{
			$gazelleRequest->setQueryParam($key, urlencode($value));
		}
		
		if ($request->hasBody())
		{
			$gazelleRequest->setBody($request->getBody());
		}
		
		return $gazelleRequest->send();
	}
	
	private function parse(IRawRequest $request, IResponse $response): IRawResponse
	{
		return new RawResponse(
			$request,
			$response->getCode(), 
			$response->getHeaders(),
			$response->hasBody() ? $response->getBody() : null
		);
	}
	
	
	public function __construct(Gazelle $gazelle)
	{
		$this->gazelle = $gazelle;
	}


	public function request($request, string $method = '', array $params = []): IRawResponse
	{
		$request = RawRequest::toRequest($request, $method, $params);
		
		try
		{
			$response = $this->send($request);
		}
		catch (ResponseException $resE)
		{
			$response = $resE->response();
		}
		catch (RequestException $reqE)
		{
			throw new ServerUnreachableException($reqE->getMessage(), $reqE->getCode(), $reqE);
		}
		catch (\Exception $e)
		{
			throw new SnuggleException($e->getMessage(), $e->getCode(), $e);
		}
		
		return $this->parse($request, $response);
	}
}