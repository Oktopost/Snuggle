<?php
namespace Snuggle\Commands\Conflict\Strategy;


use Snuggle\Base\IConnection;
use Snuggle\Commands\Conflict\IResolutionStrategy;


abstract class AbstractStrategy implements IResolutionStrategy
{
	/** @var IConnection */
	private $conn;
	
	
	protected function conn(): IConnection
	{
		return $this->conn;
	}
	
	
	public function __construct(IConnection $conn)
	{
		$this->conn = $conn;
	}
}