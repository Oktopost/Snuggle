<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Connection\Response\IRawResponse;


trait TQuery
{
	private function executeRequest(): IRawResponse
	{
		return $this->execute();
	}
	
	
	public function queryCode(): int
	{
		return $this->executeRequest()->getCode();
	}
	
	public function queryHeaders(): array
	{
		return $this->executeRequest()->getHeaders();
	}
	
	public function queryBody(): ?string
	{
		$body = $this->executeRequest()->getBody();
		return $body ? $body->getString() : null;
	}
	
	public function queryJson($asArray = true)
	{
		return $this->executeRequest()->getJsonBody($asArray);
	}
	
	public function queryBool(): bool
	{
		return $this->executeRequest()->isSuccessful();
	}
}