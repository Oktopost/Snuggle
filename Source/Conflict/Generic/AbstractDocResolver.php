<?php
namespace Snuggle\Conflict\Generic;


use Snuggle\Base\Conflict\Resolvers\Generic\ICallbackResolver;
use Snuggle\Core\ConflictBehavior;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Conflict\IConflictResolutionTemplate;
use Snuggle\Base\Conflict\Commands\Generic\IGetRevConflictCommand;
use Snuggle\Base\Conflict\Resolvers\Generic\IMergeResolver;
use Snuggle\Base\Conflict\Resolvers\Generic\ISimpleResolver;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\CmdGet;

use Snuggle\Conflict\ResolutionMediator;

use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\Http\NotFoundException;


abstract class AbstractDocResolver implements 
	IConflictResolutionTemplate, 
	ICallbackResolver,
	ISimpleResolver, 
	IMergeResolver
{
	private $callback = null;
	
	/** @var ResolutionMediator */
	private $mediator;
	
	/** @var IConnection */
	private $connection;
	
	
	protected abstract function getCommand(): IGetRevConflictCommand;
	
	
	protected function getRevision(IRawResponse $response): string 
	{
		$etag = $this->getGetCommand($this->getCommand())->queryETag();
		
		if (!isset($etag))
			throw new NotFoundException($response, 'Could not get revision for conflicting document');
		
		$rev = jsondecode($etag);
		
		if (!is_string($rev))
		{
			throw new NotFoundException(
				$response, 
				'Malformed revision format ' . base64_encode($etag));
		}
		
		return $rev;
	}
	
	protected function reRunForRevision(string $revision): IRawResponse
	{
		$command = clone $this->getCommand();
		$command->rev($revision);
		
		return $this->executeRequest($command->assemble());
	}
	
	protected function callback(): callable 
	{
		return $this->callback;
	}
	
	protected function getGetCommand(IGetRevConflictCommand $command): ICmdGet
	{
		$get = new CmdGet($this->connection);
		$readQuorum = $command->getReadQuorum();
		
		if ($readQuorum)
		{
			$get->quorumRead($readQuorum);
		}
		
		return $get->doc($command->getDB(), $command->getDocID());
	}
	
	protected function executeRequest(IRawRequest $request): IRawResponse
	{
		return $this->mediator->execute($request);
	}
	
	protected function setStrategy(string $strategy): void
	{
		$this->callback = null;
		$this->mediator->setStrategy($strategy);
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
		$this->mediator = new ResolutionMediator($connection, $this);
	}
	
	
	public function mergeNew(IRawResponse $response, ConflictException $e): IRawResponse
	{
		throw new FatalSnuggleException('This strategy is not supported by this command');
	}
	
	public function mergeOver(IRawResponse $response, ConflictException $e): IRawResponse
	{
		throw new FatalSnuggleException('This strategy is not supported by this command');
	}
	
	public function ignoreConflict(): void
	{
		$this->setStrategy(ConflictBehavior::IGNORE);
	}
	
	public function overrideConflict(): void
	{
		$this->setStrategy(ConflictBehavior::OVERRIDE);
	}
	
	public function failOnConflict(): void
	{
		$this->setStrategy(ConflictBehavior::FAIL);
	}
	
	public function mergeNewOnConflict(): void
	{
		$this->setStrategy(ConflictBehavior::MERGE_NEW);
	}
	
	public function mergeOverOnConflict(): void
	{
		$this->setStrategy(ConflictBehavior::MERGE_OVER);
	}
	
	public function resolveConflict(callable $callback): void
	{
		$this->setStrategy(ConflictBehavior::RESOLVE);
		$this->callback = $callback;
	}
}