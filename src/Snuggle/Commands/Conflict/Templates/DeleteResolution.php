<?php
namespace Snuggle\Commands\Conflict\Templates;


use Snuggle\Commands\Conflict\Generic\DocResolutionTemplate;
use Snuggle\Core\ConflictBehavior;
use Snuggle\Base\Commands\Conflict\IDocConflictableCommand;
use Snuggle\Base\Commands\Conflict\IDeleteResolution;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;


class DeleteResolution extends DocResolutionTemplate implements IDeleteResolution
{
	/** @var callable */
	private $callback = null;
	
	
	protected function resolve(ConflictException $e, IDocConflictableCommand $command): IRawResponse
	{
		$doc = $this->cmdGet($command)->queryDoc();
		
		$callback = $this->callback;
		
		if ($callback($doc))
		{
			$command = clone $command;
			$command->rev($doc->Rev);
			
			return $this->execute($command);
		}
		else
		{
			return $e->getResponse();
		}
	}
	
	
	public function setStrategy(string $strategy): void
	{
		parent::setStrategy($strategy);
		
		if ($strategy != ConflictBehavior::RESOLVE)
			$this->callback = null;
	}
	
	
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 */
	public function resolveConflict(callable $callback): void
	{
		$this->setStrategy(ConflictBehavior::RESOLVE);
		$this->callback = $callback;
	}
}