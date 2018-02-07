<?php
namespace Snuggle\Commands;


use Snuggle\Core\DB\DBInfo;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;

use Snuggle\Connection\Method;
use Snuggle\Connection\Parsers\OkResponse;
use Snuggle\Connection\Parsers\DB\DBInfoParser;

use Snuggle\Exceptions\Http\NotFoundException;


class CmdDB implements ICmdDB
{
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function create(string $name, ?int $shards = null): void
	{
		$params = [];
		
		if ($shards)
			$params['q'] = $shards;
		
		$result = $this->connection->request(
			$name, 
			Method::PUT, 
			$params
		);
		
		OkResponse::parse($result);
	}
	
	public function drop(string $name): void
	{
		$result = $this->connection->request($name, Method::DELETE);
		OkResponse::parse($result);
	}
	
	public function exists(string $name): bool
	{
		try
		{
			$this->connection->request($name, Method::HEAD);
		}
		catch (NotFoundException $e)
		{
			return false;
		}
		
		return true;
	}
	
	public function info(string $name): DBInfo
	{
		return DBInfoParser::parse($this->connection->request($name));
	}
}