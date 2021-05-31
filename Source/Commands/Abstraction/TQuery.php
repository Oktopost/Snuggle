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
		/** @var IRawResponse $res */
		$res = $this->executeSafe($e);
		
		if (is_null($res))
			throw $e;
			
		return $res->getCode();
	}
	
	public function queryHeaders(): array
	{
		return $this->executeRequest()->getHeaders();
	}
	
	public function queryETag(): ?string
	{
		$headers = $this->queryHeaders();
		return $headers['etag'] ?? $headers['ETag'] ?? null;
	}
	
	public function queryBody(): ?string
	{
		return $this->executeRequest()->getRawBody();
	}
	
	public function queryJson($asArray = true)
	{
		return $this->executeRequest()->getJsonBody($asArray);
	}
	
	public function queryBool(): bool
	{
		/** @var IRawResponse $res */
		$res = $this->executeSafe($e);
		return $res ? $res->isSuccessful() : false;
	}
}