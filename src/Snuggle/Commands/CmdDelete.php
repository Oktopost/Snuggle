<?php
namespace Snuggle\Commands;


use Snuggle\Core\ConflictBehavior;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\IDocCommand;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Common\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Connection\Method;
use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\Http\NotFoundException;


class CmdDelete implements ICmdDelete
{
	use TQuery;
	use TExecuteSafe;
	
	
	private $db;
	private $id;
	
	private $params = [];
	private $conflictBehavior	= ConflictBehavior::OVERRIDE;
	private $failOnNotFound		= false;
	
	/** @var callable|null */
	private $resolveCallback	= null;
	
	
	private function validate(): void
	{
		if ($this->db && $this->id)
			return;
		
		throw new FatalSnuggleException('DB name AND document id must be set');
	}
	
	private function uri(): string
	{
		return $this->db . '/' . $this->id;
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
	 * @return ICmdDelete|static
	 */
	public function ignoreConflict(): ICmdDelete
	{
		$this->conflictBehavior = ConflictBehavior::IGNORE;
		$this->resolveCallback = null;
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function overrideConflict(): ICmdDelete
	{
		$this->conflictBehavior = ConflictBehavior::OVERRIDE;
		$this->resolveCallback = null;
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function failOnConflict(): ICmdDelete
	{
		$this->conflictBehavior = ConflictBehavior::FAIL;
		$this->resolveCallback = null;
		return $this;
	}
	
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 * @return ICmdDelete|static
	 */
	public function resolveConflict(callable $callback): ICmdDelete
	{
		$this->resolveCallback = $callback;
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
	
	/**
	 * @param string $db
	 * @return IDocCommand|static
	 */
	public function from(string $db): IDocCommand
	{
		$this->db = $db;
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
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID.
	 * @return IDocCommand|static
	 */
	public function doc(string $target, ?string $id = null): IDocCommand
	{
		if ($id)
		{
			$this->db = $target;
			$this->id = $id;
		}
		else
		{
			$this->id = $target;
		}
		
		return $this;
	}
	
	private function handleConflict(ConflictException $e): IRawResponse
	{
		switch ($this->conflictBehavior)
		{
			case ConflictBehavior::IGNORE:
				return $e->getResponse();
				
			case ConflictBehavior::FAIL:
				throw $e;
			
			case ConflictBehavior::OVERRIDE:
				
		}
	}
	
	public function execute(): IRawResponse
	{
		$this->validate();
		
		try
		{
			$request = $this->createRequest($this->uri(), Method::DELETE, $this->params);
			return $this->getConnection()->request($request);
		}
		catch (NotFoundException $e)
		{
			if ($this->failOnNotFound)
				throw $e;
			
			return $e->getResponse();
		}
		catch (ConflictException $e)
		{
			return $this->handleConflict($e);
		}
	}
	
	public function __clone() {}
}