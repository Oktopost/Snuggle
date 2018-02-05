<?php
namespace Snuggle\Commands\Conflict\Strategy;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Commands\Conflict\ISingleDocCommand;
use Snuggle\Commands\Conflict\IResolutionStrategy;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Connection\Request\RawRequest;


class DocOverrideStrategy implements IResolutionStrategy
{
	/** @var RawRequest */
	private $request;
	
	/** @var RawRequest */
	private $command;
	
	/** @var bool */
	private $isCloned = false;
	
	
	private function getNewRevision(): void
	{
		if (!$this->isCloned)
		{
			$this->request = clone $this->request;
			$this->isCloned = true;
		}
	}
	
	
	public function __construct(ISingleDocCommand $command)
	{
		$this->command = $command;
		
	}
	
	public function setRequest(RawRequest $request): DocOverrideStrategy
	{
		$this->request = $request;
		return $this;
	}
	
	
	public function execute(): IRawResponse
	{
		$response = null;
		
		while (true)
		{
			try
			{
				$response = $this->conn()->request($this->request);
			}
			catch (ConflictException $e)
			{
				$this->getNewRevision();
			}
		}
		
		return $response;
	}
}