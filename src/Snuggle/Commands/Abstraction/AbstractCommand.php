<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\ICommand;
use Snuggle\Base\IConnection;


class AbstractCommand implements ICommand
{
	/** @var IConnection */
	private $connection;
	
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	
	public function __clone()
	{
		
	}
	
	public function setConnection(IConnection $connection): void
	{
		$this->connection = $connection;
	}
}