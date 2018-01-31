<?php
namespace Snuggle\Base\Connection\Request;


interface IRawRequest
{
	public function getURI(): string;
	public function getHeaders(): array;
	public function getMethod(): string;
	
	public function getQueryParams(): array;
	public function hasQueryParams(): bool;
	
	public function getBody(): ?string;
	public function hasBody(): bool;
}