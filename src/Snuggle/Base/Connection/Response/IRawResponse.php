<?php
namespace Snuggle\Base\Connection\Response;


use Snuggle\Base\Connection\Request\IRawRequest;


interface IRawResponse
{
	public function request(): IRawRequest;
	
	public function getBody(): ?IBody;
	public function hasBody(): bool;
	public function getRawBody(): ?string;
	public function getJsonBody($asArray = true);
	
	public function getCode(): int;
	public function isSuccessful(): bool;
	public function isFailed(): bool;
	public function isNotFound(): bool;
	public function isServerError(): bool;
	
	public function getHeaders(): array;
	public function getHeader(string $name): ?string;
	public function hasHeader(string $name): bool;
}