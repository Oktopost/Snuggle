<?php
namespace Snuggle\Connection;


use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;

use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Base\Commands\ICmdBulkInsert;

use Snuggle\Base\Factories\ICommandFactory;


class Connector implements IConnector
{
	/** @var ICommandFactory */
	private $factory;
	
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(?ICommandFactory $factory = null, ?IConnection $connection = null)
	{
		if ($factory)
			$this->factory = $factory;
		
		if ($connection)
			$this->connection = $connection;
	}
	
	
	public function setFactory(ICommandFactory $factory): void
	{
		$this->factory = $factory;
	}
	
	public function setConnection(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function db(): ICmdDB
	{
		return $this->factory->db($this->connection); 
	}
	
	public function direct(): ICmdDirect
	{
		return $this->factory->direct($this->connection); 
	}
	
	public function server(): ICmdServer
	{
		return $this->factory->server($this->connection);
	}
	
	public function get(): ICmdGet
	{
		return $this->factory->get($this->connection);
	}
	
	public function delete(): ICmdDelete
	{
		return $this->factory->delete($this->connection); 
	}
	
	public function insert(): ICmdInsert
	{
		return $this->factory->insert($this->connection);
	}
	
	public function store(): ICmdStore
	{
		return $this->factory->store($this->connection);
	}
	
	public function bulkInsert(): ICmdBulkInsert
	{
		return $this->factory->bulkInsert($this->connection);
	}
}