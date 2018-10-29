<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TDocCommand;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Commands\Abstraction\TRefreshView;
use Snuggle\Commands\Abstraction\TQueryRevision;

use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;

use Snuggle\Exceptions\FatalSnuggleException;


class CmdInsert implements ICmdInsert
{
	use TQuery;
	use TQueryRevision;
	use TExecuteSafe;
	use TDocCommand;
	use TRefreshView;
	
	
	private $db	= null;
	private $id = null;
	
	private $data		= [];
	private $asBatch	= false;
	
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
		$this->setRefreshConnection($connection);
	}
	
	
	/**
	 * @param string $db
	 * @param string|null $id
	 * @return ICmdInsert|static
	 */
	public function into(string $db, string $id = null): ICmdInsert
	{
		$this->db = $db;
		$this->setRefreshDB($db);
		
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
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function data($data, $value = null): ICmdInsert
	{
		if ($data instanceof \stdClass)
			$this->data = (array)$data;
		else if (is_array($data))
			$this->data = $data;
		else
			$this->data[$data] = $value;
		
		return $this;
	}
	
	public function execute(): IRawResponse
	{
		if (!$this->db)
			throw new FatalSnuggleException('DB name must be set');
		
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
		
		$result = $this->connection->request($request);
		
		if ($result->isSuccessful())
		{
			$this->refreshViews();
		}
		
		return $result;
	}
	
	
	/**
	 * @deprecated
	 * @param array|string $data
	 * @param mixed|null $value
	 * @return ICmdInsert|static
	 */
	public function document($data, $value = null): ICmdInsert
	{
		return $this->data($data, $value);
	}
	
	/**
	 * @deprecated 
	 * @param string $id
	 * @return ICmdInsert
	 */
	public function setID(string $id): ICmdInsert
	{
		$this->doc($id);
		return $this;
	}
}