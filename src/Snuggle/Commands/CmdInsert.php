<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Commands\Abstraction\TQueryRevision;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;

use Snuggle\Exceptions\SnuggleException;


class CmdInsert implements ICmdInsert
{
	use TQuery;
	use TQueryRevision;
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
	public function document($data, $value = null): ICmdInsert
	{
		if (is_array($data))
			$this->data = $data;
		else
			$this->data[$data] = $value;
		
		return $this;
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
}