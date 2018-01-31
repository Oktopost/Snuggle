<?php
namespace Snuggle;


use Snuggle\Base\Commands\ICmdDirect;
use Snuggle\Base\ICommand;
use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Factories\ICommandFactory;


class Connector implements IConnector
{
	/** @var ICommandFactory */
	private $factory;
	
	/** @var IConnection */
	private $connection;
	
	
	private function setup(ICommand $command): ICommand
	{
		$command->setConnection($this->connection);
		return $command;
	}
	
	
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
		return $this->setup($this->factory->db()); 
	}
	
	public function direct(): ICmdDirect
	{
		return $this->setup($this->factory->direct()); 
	}
}