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
	private $forceResolveUnmodified = false;
	
	/** @var IStoreConflictCommand */
	private $command;
	
	
	private function merge(IRawResponse $response, callable $mergeCallback): IRawResponse
	{
		$doc = $this->getGetCommand($this->command)->queryDoc();
		$new = $this->command->getBody();
		$existing = $doc->Data;
		
		$merged = $mergeCallback($existing, $new);
		
		if (!$this->forceResolveUnmodified && $merged === $new)
		{
			return $response;
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
		$response = $this->getGetCommand($this->command)->execute();
		
		$existingDoc = SingleDocParser::parse($response);
		$targetDoc = $this->getNewDocument();
		
		$callback = $this->callback();
		
		/** @var Doc|null $doc */
		$doc = $callback($existingDoc, $targetDoc);
		
		if ($doc)
		{
			return $this->store($doc->Rev, $doc->Data);
		}
		else
		{
			return $response;
		}
	}
	
	public function mergeNew(IRawResponse $response, ConflictException $e): IRawResponse
	{
		return $this->merge($response, function (array $existing, array $new): array
		{
			return RecursiveMerge::merge($new, $existing); 
		});
	}
	
	public function mergeOver(IRawResponse $response, ConflictException $e): IRawResponse
	{
		return $this->merge($response, function (array $existing, array $new): array
		{
			return RecursiveMerge::merge($existing, $new); 
		});
	}
	
	public function override(IRawResponse $response, ConflictException $e): IRawResponse
	{
		$existingCommand = $this->getGetCommand($this->getCommand());
		
		if (!$this->forceResolveUnmodified)
		{
			$existing = $existingCommand->queryDoc();
			$new = $this->command->getBody();
			
			if ($existing->Data == $new)
			{
				return $response;
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
	
	public function forceResolveUnmodified(bool $force = true): void
	{
		$this->forceResolveUnmodified = $force;
	}
}