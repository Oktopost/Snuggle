<?php
namespace Snuggle\Conflict\Resolvers;


use Snuggle\Base\Conflict\Commands\Generic\IGetDocConflictCommand;

use Snuggle\Base\Conflict\Commands\IDeleteConflictCommand;
use Snuggle\Base\Conflict\Resolvers\IDeleteDocResolver;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Conflict\Generic\AbstractDocResolver;
use Snuggle\Exceptions\Http\ConflictException;


class DeleteDocResolver extends AbstractDocResolver implements IDeleteDocResolver
{
	/** @var IDeleteConflictCommand */
	private $command;
	
	
	protected function getCommand(): IGetDocConflictCommand
	{
		return $this->command;
	}
	
	
	public function resolve(IRawResponse $response, ConflictException $e): IRawResponse
	{
		$doc = $this->getGetCommand($this->command)->queryDoc();
		$callback = $this->callback();
		
		if ($callback($doc))
		{
			$command = clone $this->command;
			$command->rev($doc->Rev);
			
			return $this->executeRequest($command->assemble());
		}
		else
		{
			return $e->getResponse();
		}
	}
	
	
	public function execute(IDeleteConflictCommand $command): IRawResponse
	{
		$this->command = $command;
		return $this->executeRequest($command->assemble());
	}
}