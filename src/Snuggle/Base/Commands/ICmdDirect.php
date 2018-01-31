<?php
namespace Snuggle\Base\Commands;


interface ICmdDirect extends IExecutable, IQuery
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
}