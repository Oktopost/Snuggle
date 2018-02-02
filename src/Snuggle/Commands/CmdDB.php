<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\ICmdDB;

use Snuggle\Core\DB\DBInfo;
use Snuggle\Connection\Method;
use Snuggle\Connection\Parsers\OkResponse;
use Snuggle\Connection\Parsers\DB\DBInfoParser;

use Snuggle\Commands\Abstraction\AbstractCommand;
use Snuggle\Exceptions\Http\NotFoundException;


class CmdDB extends AbstractCommand implements ICmdDB
{
	public function create(string $name, ?int $shards = null): void
	{
		$params = [];
		
		if ($shards)
			$params['q'] = $shards;
		
		$result = $this->executeRequest($name, Method::PUT, $params);
		OkResponse::parse($result);
	}
	
	public function drop(string $name): void
	{
		OkResponse::parse($this->executeRequest($name, Method::DELETE));
	}
	
	public function exists(string $name): bool
	{
		try
		{
			$this->executeRequest($name, Method::HEAD);
		}
		catch (NotFoundException $e)
		{
			return false;
		}
		
		return true;
	}
	
	public function info(string $name): DBInfo
	{
		return DBInfoParser::parse($this->executeRequest($name));
	}
}