<?php
namespace Snuggle\Commands\Conflict\Generic;


use Snuggle\Core\ConflictBehavior;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\Conflict\IDocResolution;
use Snuggle\Base\Commands\Conflict\IDocConflictableCommand;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\CmdGet;

use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\Http\NotFoundException;


abstract class DocResolutionTemplate implements IDocResolution
{
	private $strategy = ConflictBehavior::FAIL;
	
	/** @var IConnection */
	private $connection;
	
	
	private function handleException(ConflictException $e, IDocConflictableCommand $command): IRawResponse
	{
		switch ($this->strategy)
		{
			case ConflictBehavior::FAIL:
				throw $e;
			
			case ConflictBehavior::IGNORE:
				return $e->getResponse();
			
			case ConflictBehavior::OVERRIDE:
				return $this->override($e, $command);
			
			case ConflictBehavior::RESOLVE:
				return $this->resolve($e, $command);
			
			case ConflictBehavior::MERGE_NEW:
				return $this->mergeNew($e, $command);
			
			case ConflictBehavior::MERGE_OVER:
				return $this->mergeOver($e, $command);
			
			default:
				throw new FatalSnuggleException("Strategy {$this->strategy} is not valid");
		}
	}
	
	protected function cmdGet(IDocConflictableCommand $command): ICmdGet
	{
		$get = new CmdGet($this->connection);
		return $get->doc($command->getDB(), $command->getDocId());
	}
	
	protected function strategy(): string
	{
		return $this->strategy;
	}
	
	protected function connection(): IConnection
	{
		return $this->connection;
	}
	
	
	protected abstract function resolve(ConflictException $e, IDocConflictableCommand $command): IRawResponse;
	
	protected function override(ConflictException $e, IDocConflictableCommand $command): IRawResponse
	{
		$headers = $this->cmdGet($command)->queryHeaders();
		
		if (!isset($headers['rev']))
			throw new NotFoundException($e->getResponse(), 'Could not get revision for conflicting document');
		
		$rev = json_decode($headers['rev']);
		
		if (is_null($rev))
			throw new NotFoundException($e->getResponse(), 'Malformed revision format ' . base64_encode($headers['rev']));
		
		$command = clone $command;
		$command->rev($rev);
		
		return $this->execute($command);
	}
	
	protected function mergeNew(ConflictException $e, IDocConflictableCommand $command): IRawResponse 
	{
		throw new FatalSnuggleException('This strategy is not supported by this command');
	}
	
	protected function mergeOver(ConflictException $e, IDocConflictableCommand $command): IRawResponse
	{
		throw new FatalSnuggleException('This strategy is not supported by this command');
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
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
	
	public function setStrategy(string $strategy): void
	{
		$this->strategy = $strategy;
	}
	
	public function execute(IDocConflictableCommand $command): IRawResponse
	{
		$request = $command->assemble();
		
		try
		{
			return $this->connection->request($request);
		}
		catch (ConflictException $e)
		{
			return $this->handleException($e, $command);
		}
	}
}