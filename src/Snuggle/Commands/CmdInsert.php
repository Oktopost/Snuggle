<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Common\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Core\Doc;
use Snuggle\Connection\Method;
use Snuggle\Connection\Parsers\OkResponse;
use Snuggle\Connection\Request\RawRequest;

use Snuggle\Exceptions\SnuggleException;


class CmdInsert implements ICmdInsert
{
	use TQuery;
	use TExecuteSafe;
	
	
	private $db	= null;
	private $id = null;
	
	private $data		= [];
	private $asBatch	= false;
	
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	/**
	 * @param string $db
	 * @param string|null $id
	 * @return ICmdInsert|static
	 */
	public function into(string $db, string $id = null): ICmdInsert
	{
		$this->db = $db;
		
		if ($id)
			$this->id = $id;
		
		return $this;
	}
	
	/**
	 * @param bool $isAsBatch
	 * @return ICmdInsert|static
	 */
	public function asBatch($isAsBatch = true): ICmdInsert
	{
		$this->asBatch = $isAsBatch;
		return $this;
	}
	
	/**
	 * @param string $id
	 * @return ICmdInsert|static
	 */
	public function setID(string $id): ICmdInsert
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function data($data, $value = null): ICmdInsert
	{
		if (is_array($data))
			$this->data = $data;
		else
			$this->data[$data] = $value;
		
		return $this;
	}
	
	/**
	 * Return the ETag of the inserted document.
	 * @return string
	 */
	public function queryETag(): string
	{
		$tag = $this->queryHeaders()['ETag'] ?? null;
		
		if (is_null($tag))
			throw new SnuggleException('No ETag returned for new object');
		
		$tag = json_decode($tag);
		
		if (is_null($tag))
			throw new SnuggleException('Malformed ETag for new object');
		
		return $tag;
	}
	
	public function execute(): IRawResponse
	{
		if (!$this->db)
			throw new SnuggleException('DB name must be set');
		
		if ($this->id)
		{
			$request = RawRequest::create($this->db . '/' . $this->id, Method::PUT);
		}
		else
		{
			$request = RawRequest::create($this->db, Method::POST);
		}
		
		if ($this->asBatch)
		{
			$request->setQueryParams(['batch' => 'ok']);
		}
		
		$request
			->setHeader('Content-Type', 'application/json')
			->setBody($this->data);
		
		return $this->connection->request($request);
	}
	
	public function queryDoc(): Doc
	{
		$result = $this->execute();
		OkResponse::parse($result);
		
		$body = $result->getJsonBody();
		
		if (!isset($body['id']) || !isset($body['rev']))
			throw new SnuggleException('Malformed response for new document');
		
		$doc = new Doc();
		
		$doc->ID = $body['id'] ?? '';
		$doc->Rev = $body['rev'] ?? '';
		$doc->Data = $this->data;
		
		return $doc;
	}
}