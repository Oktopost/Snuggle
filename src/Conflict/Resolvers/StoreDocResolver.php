<?php
namespace Snuggle\Conflict\Resolvers;


use Snuggle\Conflict\RecursiveMerge;
use Snuggle\Core\Doc;

use Snuggle\Base\Conflict\Commands\IStoreConflictCommand;
use Snuggle\Base\Conflict\Commands\Generic\IGetRevConflictCommand;
use Snuggle\Base\Conflict\Resolvers\IStoreDocResolver;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Conflict\Generic\AbstractDocResolver;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Connection\Parsers\SingleDocParser;


class StoreDocResolver extends AbstractDocResolver implements IStoreDocResolver
{
	/** @var IStoreConflictCommand */
	private $command;
	
	
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
		$doc = $this->getGetCommand($this->command)->queryDoc();
		$data = $this->command->getBody();
		
		$data = RecursiveMerge::merge($data, $doc->Data);
		
		return $this->store($doc->Rev, $data);
	}
	
	public function mergeOver(IRawResponse $response, ConflictException $e): IRawResponse
	{
		$doc = $this->getGetCommand($this->command)->queryDoc();
		$data = $this->command->getBody();
		
		$data = RecursiveMerge::merge($data, $doc->Data);
		
		return $this->store($doc->Rev, $data);
	}
	
	
	public function execute(IStoreConflictCommand $command): IRawResponse
	{
		$this->command = $command;
		return $this->executeRequest($command->assemble());
	}
}