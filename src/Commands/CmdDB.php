<?php
namespace Snuggle\Commands;


use Snuggle\Core\DB\DBInfo;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Parsers\OkResponse;
use Snuggle\Connection\Parsers\DB\DBInfoParser;

use Snuggle\Exceptions\Http\NotFoundException;
use Snuggle\Exceptions\Http\PreconditionFailedException;


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
	
	public function createIfNotExists(string $name, ?int $shards = null): bool
	{
		$params = [];
		
		if ($shards)
			$params['q'] = $shards;
		
		try
		{
			$result = $this->connection->request(
				$name, 
				Method::PUT, 
				$params
			);
			
			OkResponse::parse($result);
			return true;
		}
		catch (PreconditionFailedException $p)
		{
			return false;
		}
	}
	
	public function drop(string $name): void
	{
		$result = $this->connection->request($name, Method::DELETE);
		OkResponse::parse($result);
	}
	
	public function dropIfExists(string $name): bool
	{
		try
		{
			$result = $this->connection->request($name, Method::DELETE);
			OkResponse::parse($result);
			return true;
		}
		catch (NotFoundException $e) 
		{
			return false;
		}
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
	
	public function compact(string $name, ?string $design = null): void
	{
		$endPoint = $name . '/_compact';
		
		if ($design)
			$endPoint .= "/$design";
		
		OkResponse::parse($this->connection->request($endPoint, Method::POST));
	}
	
	public function setRevisionsLimit(string $name, int $limit): void
	{
		$request = RawRequest::create($name . '/_revs_limit', Method::PUT)->setBody($limit);
		OkResponse::parse($this->connection->request($request));
	}
}