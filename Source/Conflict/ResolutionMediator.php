<?php
namespace Snuggle\Conflict;


use Snuggle\Base\Commands\IAssemble;
use Snuggle\Core\ConflictBehavior;

use Snuggle\Base\IConnection;
use Snuggle\Base\Conflict\IConflictResolutionTemplate;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;


class ResolutionMediator
{
	private $strategy = ConflictBehavior::FAIL;
	
	/** @var IConflictResolutionTemplate */
	private $template;
	
	/** @var IConnection */
	private $connection;
	
	
	private function handleException(ConflictException $e): IRawResponse
	{
		$response = $e->getResponse();
		
		switch ($this->strategy)
		{
			case ConflictBehavior::FAIL:
				throw $e;
			
			case ConflictBehavior::IGNORE:
				return $e->getResponse();
			
			case ConflictBehavior::OVERRIDE:
				return $this->template->override($response, $e);
			
			case ConflictBehavior::RESOLVE:;
				return $this->template->resolve($response, $e);
			
			case ConflictBehavior::MERGE_NEW:
				return $this->template->mergeNew($response, $e);
			
			case ConflictBehavior::MERGE_OVER:
				return $this->template->mergeOver($response, $e);
			
			default:
				throw new FatalSnuggleException("Strategy {$this->strategy} is not valid");
		}
	}
	
	
	public function __construct(IConnection $connection, IConflictResolutionTemplate $template)
	{
		$this->connection = $connection;
		$this->template = $template;
	}
	
	public function setStrategy(string $strategy): void
	{
		$this->strategy = $strategy;
	}
	
	
	/**
	 * @param IAssemble|IRawRequest $command
	 * @return IRawResponse
	 */
	public function execute($command): IRawResponse
	{
		if ($command instanceof IAssemble)
			$request = $command->assemble();
		else if ($command instanceof IRawRequest)
			$request = $command;
		else
			throw new FatalSnuggleException('Command must be IAssemble or IRawRequest instance');
		
		try
		{
			return $this->connection->request($request);
		}
		catch (ConflictException $e)
		{
			return $this->handleException($e);
		}
	}
}