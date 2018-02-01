<?php
namespace Snuggle\Commands\Common;


use Snuggle\Base\Connection\Response\IRawResponse;


/**
 * @method IRawResponse execute()
 */
trait TQuery
{
	public function queryCode(): int
	{
		return $this->execute()->getCode();
	}
	
	public function queryHeaders(): array
	{
		return $this->execute()->getHeaders();
	}
	
	public function queryBody(): ?string
	{
		$body = $this->execute()->getBody();
		return $body ? $body->getString() : null;
	}
	
	public function queryJson($asArray = false)
	{
		return $this->execute()->getJsonBody($asArray);
	}
}