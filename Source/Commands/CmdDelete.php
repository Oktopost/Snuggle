<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\IRevCommand;
use Snuggle\Base\Conflict\Commands\IDeleteConflictCommand;
use Snuggle\Base\Conflict\Resolvers\IDeleteDocResolver;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TDocCommand;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Commands\Abstraction\TQueryRevision;

use Snuggle\Conflict\Resolvers\DeleteDocResolver;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;

use Snuggle\Exceptions\Http\NotFoundException;


class CmdDelete implements ICmdDelete, IDeleteConflictCommand
{
	use TQuery;
	use TDocCommand;
	use TExecuteSafe;
	use TQueryRevision;
	
	
	private $params				= [];
	private $failOnNotFound		= false;
	
	/** @var IDeleteDocResolver */
	private $resolver;
	
	/** @var IConnection */
	private $connection;
	
	
	private function loadRevision(): void
	{
		$get = new CmdGet($this->connection);
		$rev = $get->doc($this->getDB(), $this->getDocID())->queryRevision();
		
		$this->rev($rev);
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
		
		$this->resolver = new DeleteDocResolver($this->connection);
		$this->resolver->overrideConflict();
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
	 * @return IRevCommand|static
	 */
	public function rev(string $rev): IRevCommand
	{
		$this->params['rev'] = $rev;
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function ignoreConflict(): ICmdDelete
	{
		$this->resolver->ignoreConflict();
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function overrideConflict(): ICmdDelete
	{
		$this->resolver->overrideConflict();
		return $this;
	}
	
	/**
	 * @return ICmdDelete|static
	 */
	public function failOnConflict(): ICmdDelete
	{
		$this->resolver->failOnConflict();
		return $this;
	}
	
	/**
	 * @param callable $callback Callback in format [(Doc $doc): bool]
	 * @return ICmdDelete|static
	 */
	public function resolveConflict(callable $callback): ICmdDelete
	{
		$this->resolver->resolveConflict($callback);
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

	public function readQuorum(int $quorum)
	{
		// TODO: Implement readQuorum() method.
	}

	public function writeQuorum(int $quorum)
	{
		// TODO: Implement writeQuorum() method.
	}

	public function quorum(int $read, int $write)
	{
		// TODO: Implement quorum() method.
	}
	
	
	public function execute(): IRawResponse
	{
		$this->requireDBAndDocID();
		
		try
		{
			return $this->resolver->execute($this);
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
		if (!isset($this->params['rev']))
			$this->loadRevision();
		
		return RawRequest::create(
			$this->uri(),
			Method::DELETE,
			$this->params
		);
	}
}