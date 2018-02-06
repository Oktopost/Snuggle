<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\IDocCommand;
use Snuggle\Base\Commands\Conflict\IDeleteResolution;
use Snuggle\Base\Commands\Conflict\IDocConflictableCommand;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Common\TQuery;
use Snuggle\Commands\Abstraction\TDocCommand;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;

use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\NotFoundException;


class CmdDelete implements ICmdDelete, IDocConflictableCommand
{
	use TQuery;
	use TDocCommand;
	use TExecuteSafe;
	
	
	private $params				= [];
	private $failOnNotFound		= false;
	
	/** @var IDeleteResolution */
	private $connection;
	
	
	private function validate(): void
	{
		if ($this->getDB() && $this->getDocID() && isset($this->params['rev']))
			return;
		
		throw new FatalSnuggleException('DB name, document id and revision must be set for the delete command');
	}
	
	
	public function __construct(IDeleteResolution $connection)
	{
		$this->connection = $connection;
		$this->connection->overrideConflict();
	}
	
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdDelete|static
	 */
	public function asBatch(bool $isAsBatch = true): ICmdDelete
	{
		if ($isAsBatch)
			$this->params['batch'] = 'ok';
		else 
			unset($this->params['batch']);
			
		return $this;
	}
	
	/**
	 * @param string $rev
	 * @return IDocCommand|static
	 */
	public function rev(string $rev): IDocCommand
	{
		$this->params['rev'] = $rev;
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function ignoreConflict(): ICmdDelete
	{
		$this->connection->ignoreConflict();
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function overrideConflict(): ICmdDelete
	{
		$this->connection->overrideConflict();
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function failOnConflict(): ICmdDelete
	{
		$this->connection->failOnConflict();
		return $this;
	}
	
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 * @return ICmdDelete|static
	 */
	public function resolveConflict(callable $callback): ICmdDelete
	{
		$this->connection->resolveConflict($callback);
		return $this;
	}
	
	/**
	 * @param bool $fail
	 * @return ICmdDelete|static
	 */
	public function failOnNotFound(bool $fail = true): ICmdDelete
	{
		$this->failOnNotFound = $fail;
		return $this;
	}
	
	public function execute(): IRawResponse
	{
		$this->validate();
		
		try
		{
			return $this->connection->execute($this);
		}
		catch (NotFoundException $e)
		{
			if ($this->failOnNotFound)
				throw $e;
			
			return $e->getResponse();
		}
	}
	
	public function assemble(): IRawRequest
	{
		return RawRequest::create(
			$this->uri(),
			Method::DELETE,
			$this->params
		);
	}
}