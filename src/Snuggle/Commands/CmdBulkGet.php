<?php
namespace Snuggle\Commands;


use Structura\Map;

use Snuggle\Core\Doc;
use Snuggle\Core\StaleBehavior;
use Snuggle\Core\Lists\AllDocsList;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdBulkGet;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Parsers\Lists\AllDocsListParser;

use Snuggle\Exceptions\FatalSnuggleException;


class CmdBulkGet implements ICmdBulkGet
{
	use TQuery;
	use TExecuteSafe;
	
	
	private $db		= null;
	private $params	= [];
	
	/** @var IConnection */
	private $connection;
	
	
	private function setJsonParam(string $key, $value, $unsetValue = null): CmdBulkGet
	{
		if ($value === $unsetValue)
			unset($this->params[$key]);
		else
			$this->params[$key] = json_encode($value);
		
		return $this;
	}
	
	private function setParam(string $key, $value): CmdBulkGet
	{
		if (is_null($value))
			unset($this->params[$key]);
		else
			$this->params[$key] = $value;
			
		return $this;
	}
	
	private function setKeysParameter(?array $value = null): CmdBulkGet
	{
		unset($this->params['key']);
		unset($this->params['keys']);
		unset($this->params['startkey']);
		unset($this->params['endkey']);
		
		if ($value)
			$this->params = array_merge($this->params, $value);
		
		return $this;
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function from(string $db): ICmdBulkGet
	{
		$this->db = $db;
		return $this;
	}
	
	public function includeConflicts(bool $include = true): ICmdBulkGet
	{
		if ($include)
			$this->includeDocs();
		
		return $this->setJsonParam('conflicts', $include, false);
	}
	
	public function includeDocs(bool $include = true): ICmdBulkGet
	{
		return $this->setJsonParam('include_docs', $include, false);
	}
	
	public function stale(?string $behavior = StaleBehavior::OK): ICmdBulkGet
	{
		return $this->setParam('stale', $behavior);
	}
	
	public function key(?string $key): ICmdBulkGet
	{
		return $this->setKeysParameter($key ? ['key' => json_encode($key)] : null);
	}
	
	public function keys(?array $keys): ICmdBulkGet
	{
		// Keys are not escaped because this value is passed in the body.
		return $this->setKeysParameter($keys ? ['keys' => $keys] : null);
	}
	
	public function startKey(?string $startKey): ICmdBulkGet
	{
		unset($this->params['key']);
		unset($this->params['keys']);
		
		return $this->setJsonParam('startkey', $startKey);
	}
	
	public function endKey(?string $endKey): ICmdBulkGet
	{
		unset($this->params['key']);
		unset($this->params['keys']);
		
		return $this->setJsonParam('endkey', $endKey);
	}
	
	public function inclusiveEndKey(bool $isInclusive = true): ICmdBulkGet
	{
		return $this->setJsonParam('inclusive_end', $isInclusive, true);
	}
	
	public function updateSeq(bool $seq = true): ICmdBulkGet
	{
		return $this->setJsonParam('update_seq', $seq, false);
	}
	
	public function limit(?int $limit = 100): ICmdBulkGet
	{
		return $this->setParam('limit', $limit);
	}
	
	public function skip(?int $skip = 100): ICmdBulkGet
	{
		return $this->setParam('skip', $skip);
	}
	
	public function page(int $page, int $perPage = 100): ICmdBulkGet
	{
		$skip = $perPage * $page;
		
		return $this
			->limit($perPage)
			->skip($skip);
	}
	
	public function descending(bool $isDesc = true): ICmdBulkGet
	{
		return $this->setJsonParam('descending', $isDesc, false);
	}
	
	
	public function execute(): IRawResponse
	{
		if (!$this->db)
		{
			throw new FatalSnuggleException('Database name not set. ' . 
				'Method `from` must be called before executing the query');
		}
		
		$keys	= null;
		$method = Method::GET;
		$params = $this->params;
		
		if (isset($params['keys']))
		{
			$method = Method::POST;
			$keys	= $params['keys'];
			
			unset($params['keys']);
		}
		
		$request = RawRequest::create("/{$this->db}/_all_docs", $method, $params);
		
		if ($keys)
		{
			$request->setBody(['keys' => $keys]);
		}
		
		return $this->connection->request($request);
	}
	
	
	public function queryList(): AllDocsList
	{
		return AllDocsListParser::parseResponse($this->execute());
	}
	
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array
	{
		return AllDocsListParser::getDocuments($this->queryJson());
	}
	
	/**
	 * @return string[]|Map
	 */
	public function queryRevisions(): Map
	{
		$map = new Map();
		
		$command = clone $this;
		$command
			->updateSeq(false)
			->includeDocs(false);
		
		$result = $command->queryJson();
		
		foreach ($result['rows'] as $row)
		{
			if (!isset($row['id']) || !isset($row['value']['rev']))
				continue;
			
			$map[$row['id']] = $row['value']['rev'];
		}
		
		return $map;
	}
	
	/**
	 * @return Doc[]|Map
	 */
	public function queryMap(): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		foreach ($docs as $doc)
		{
			$map->add($doc->ID, $doc);
		}
		
		return $map;
	}
	
	/**
	 * @param string $field
	 * @return Doc[]|Map
	 */
	public function queryMapBy(string $field): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		$fields = explode('.', $field);
		
		foreach ($docs as $doc)
		{
			$map->add($doc->getKey($fields, ''), $doc);
		}
		
		return $map;
	}
	
	/**
	 * @param string $field
	 * @return Doc[][]|Map
	 */
	public function queryGroupBy(string $field): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		$fields = explode('.', $field);
		
		foreach ($docs as $doc)
		{
			$key = $doc->getKey($fields, '');
			
			if (!$map->has($key))
			{
				$map->add($key, [$doc]);
			}
			else
			{
				$map->add($key, array_merge($map->get($key), [$doc]));
			}
		}
		
		return $map;
	}
}