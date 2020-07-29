<?php
namespace Snuggle\Conflict\Resolvers;


use Snuggle\Core\Doc;

use Snuggle\Base\Conflict\Commands\IStoreConflictCommand;
use Snuggle\Base\Conflict\Commands\Generic\IGetRevConflictCommand;
use Snuggle\Base\Conflict\Resolvers\IStoreDocResolver;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Conflict\RecursiveMerge;
use Snuggle\Conflict\Generic\AbstractDocResolver;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Connection\Parsers\SingleDocParser;


class StoreDocResolver extends AbstractDocResolver implements IStoreDocResolver
{
	private $forceUpdateUnmodified = false;
	
	/** @var IStoreConflictCommand */
	private $command;
	
	
	private function getExistingDoc(?IRawResponse &$response): ?Doc
	{
		$response = $this->getGetCommand($this->command)->execute();
		return SingleDocParser::parse($response);
	}
	
	private function merge(callable $mergeCallback): IRawResponse
	{
		$doc = $this->getExistingDoc($exisitngResponse);
		$new = $this->command->getBody();
		$existing = $doc->Data;
		
		$merged = $mergeCallback($existing, $new);
		
		if (!$this->forceUpdateUnmodified && $doc->isDataEqualsTo($merged))
		{
			return $exisitngResponse;
		}
		
		return $this->store($doc->Rev, $merged);
	}
	
	private function getNewDocument(): Doc
	{
		$doc = new Doc();
		
		$doc->ID = $this->command->getDocID();
		$doc->Rev = $this->command->getRev();
		$doc->Data = $this->command->getBody();
		
		return $doc;
	}
	
	private function store(string $rev, array $newData): IRawResponse
	{
		$command = clone $this->command;
		
		$command->rev($rev);
		$command->setBody($newData);
		
		return $this->executeRequest($command->assemble());
	}
	
	
	protected function getCommand(): IGetRevConflictCommand
	{
		return $this->command;
	}
	
	
	public function resolve(IRawResponse $response, ConflictException $e): IRawResponse
	{
		$existingDoc = $this->getExistingDoc($existingResponse);
		$existingData = $existingDoc->Data;
		$targetDoc = $this->getNewDocument();
		
		$callback = $this->callback();
		
		/** @var Doc|null $doc */
		$doc = $callback($existingDoc, $targetDoc);
		
		if ($doc)
		{
			if (!$this->forceUpdateUnmodified && $doc->isDataEqualsTo($existingData))
			{
				return $existingResponse;
			}
			
			return $this->store($doc->Rev, $doc->Data);
		}
		else
		{
			return $existingResponse;
		}
	}
	
	public function mergeNew(IRawResponse $response, ConflictException $e): IRawResponse
	{
		return $this->merge(function (array $existing, array $new): array
		{
			return RecursiveMerge::merge($new, $existing); 
		});
	}
	
	public function mergeOver(IRawResponse $response, ConflictException $e): IRawResponse
	{
		return $this->merge(function (array $existing, array $new): array
		{
			return RecursiveMerge::merge($existing, $new); 
		});
	}
	
	public function override(IRawResponse $response, ConflictException $e): IRawResponse
	{
		if (!$this->forceUpdateUnmodified)
		{
			$existing = $this->getExistingDoc($existingResponse);
			$new = $this->command->getBody();
			
			if ($existing->isDataEqualsTo($new))
			{
				return $existingResponse;
			}
			
			$revision = $existing->Rev;
		}
		else
		{
			$revision = $this->getRevision($response);
		}
		
		return $this->reRunForRevision($revision);
	}
	
	public function execute(IStoreConflictCommand $command): IRawResponse
	{
		$this->command = $command;
		return $this->executeRequest($command->assemble());
	}
	
	public function forceUpdateUnmodified(bool $force = true): void
	{
		$this->forceUpdateUnmodified = $force;
	}
}