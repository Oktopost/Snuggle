<?php
namespace Snuggle\Commands;


use Snuggle\Core\DB\DBInfo;
use Snuggle\Core\DB\DDocInfo;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdDB;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Parsers\OkResponse;
use Snuggle\Connection\Parsers\DB\DBInfoParser;
use Snuggle\Connection\Parsers\DB\DDocInfoParser;

use Snuggle\Exceptions\Http\NotFoundException;
use Snuggle\Exceptions\Http\PreconditionFailedException;
use Snuggle\Exceptions\Http\UnexpectedHttpResponseException;

use Structura\Strings;


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
	
	public function designDocInfo(string $dbName, string $dDocName): DDocInfo
	{
		return DDocInfoParser::parse($this->connection->request("/$dbName/_design/$dDocName/_info"));
	}
	
	/**
	 * @param string $dbName
	 * @return string[]
	 */
	public function designDocs(string $dbName): array
	{
		$data	= $this->connection->request("$dbName/_design_docs");
		$body	= $data->getJsonBody();
		$ids	= [];
		
		if (!isset($body['rows']))
			throw new UnexpectedHttpResponseException($data, 'Expecting rows `key` in response body');
		
		foreach ($body['rows'] as $row)
		{
			$ids[] = Strings::shouldNotStartWith($row['id'], '_design/');
		}
		
		return $ids;
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