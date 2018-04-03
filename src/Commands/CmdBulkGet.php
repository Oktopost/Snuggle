<?php
namespace Snuggle\Commands;


use Snuggle\Core\StaleBehavior;
use Snuggle\Core\Lists\ViewList;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdBulkGet;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\BulkGet\TQueryRows;
use Snuggle\Commands\BulkGet\TQueryDocs;
use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Parsers\Lists\ViewListParser;

use Snuggle\Exceptions\FatalSnuggleException;

use Structura\Map;


class CmdBulkGet implements ICmdBulkGet
{
	use TQuery;
	use TExecuteSafe;
	
	use TQueryRows;
	use TQueryDocs;
	
	
	private $db		= null;
	private $design	= null;
	private $view	= null;
	private $params	= [];
	
	/** @var IConnection */
	private $connection;
	
	
	private function setJsonParam(string $key, $value, $unsetValue = null): CmdBulkGet
	{
		if ($value === $unsetValue)
			unset($this->params[$key]);
		else
			$this->params[$key] = jsonencode($value);
		
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
	
	
	public function from(string $db, ?string $design = null, ?string $view = null): ICmdBulkGet
	{
		$this->db = $db;
		$this->design = $design;
		$this->view = $view;
		
		return $this;
	}
	
	public function view(string $design, string $view): ICmdBulkGet
	{
		$this->design = $design;
		$this->view = $view;
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
		return $this->setKeysParameter($key ? ['key' => jsonencode($key)] : null);
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
		
		if ($this->view)
		{
			$uri = "/{$this->db}/_design/{$this->design}/_view/{$this->view}";
		}
		else
		{
			$uri = "/{$this->db}/_all_docs";
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
		
		$request = RawRequest::create($uri, $method, $params);
		
		if ($keys)
		{
			$request->setBody(['keys' => $keys]);
		}
		
		return $this->connection->request($request);
	}
	
	
	public function queryList(): ViewList
	{
		return ViewListParser::parseResponse($this->execute());
	}
	
	/**
	 * @return bool
	 */
	public function queryExists(): bool
	{
		$res = (clone $this)
			->limit(1)
			->queryList();
		
		return $res->count() > 0; 
	}
	
	/**
	 * @return string[]|Map
	 */
	public function queryRevisions(): Map
	{
		$map = new Map();
		
		$command = clone $this;
		$command
			->from($this->db)
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
}