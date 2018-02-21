<?php
namespace Snuggle\Base\Commands;


interface ICmdDirect extends IExecute, IQuery
{
	public function setBody($body): ICmdDirect;
	public function setHeader(string $name, string $value): ICmdDirect;
	public function setHeaders(array $headers): ICmdDirect;
	public function setURI(string $uri): ICmdDirect;
	public function setQueryParam(string $param, $value): ICmdDirect;
	public function setJsonQueryParam(string $param, $value): ICmdDirect;
	public function setQueryParams(array $params): ICmdDirect;
	public function setJsonQueryParams(array $params): ICmdDirect;
	public function setMethod(string $method): ICmdDirect;
	
	public function setGET(?string $uri = null, array $params = []): ICmdDirect;
	public function setHEAD(?string $uri = null, array $params = []): ICmdDirect;
	public function setPUT(?string $uri = null, array $params = [], string $body = null): ICmdDirect;
	public function setPOST(?string $uri = null, array $params = [], string $body = null): ICmdDirect;
	public function setDELETE(?string $uri = null, array $params = []): ICmdDirect;
}