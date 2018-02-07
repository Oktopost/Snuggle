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
	
	public function setGET(): ICmdDirect;
	public function setHEAD(): ICmdDirect;
	public function setPUT(): ICmdDirect;
	public function setPOST(): ICmdDirect;
	public function setDELETE(): ICmdDirect;
}