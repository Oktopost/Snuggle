<?php
namespace Snuggle\Commands;


use Snuggle\Core\Doc;

use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdBulkInsert;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;

use Snuggle\Connection\Request\RawRequest;
use Snuggle\Connection\Parsers\SingleDocParser;

use Snuggle\Exceptions\HttpException;
use Snuggle\Exceptions\FatalSnuggleException;


class CmdBulkInsert implements ICmdBulkInsert
{
	use TQuery;
	use TExecuteSafe;
	
	
	private $db;
	private $payload = [];
	
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	/**
	 * @param string $db
	 * @return ICmdBulkInsert|static
	 */
	public function into(string $db): ICmdBulkInsert
	{
		$this->db = $db;
		return $this;
	}
	
	/**
	 * @param array|\stdClass
	 * @return ICmdBulkInsert|static
	 */
	public function data($document): ICmdBulkInsert
	{
		$this->payload[] = $document;
		return $this;
	}
	
	/**
	 * @param array $documents []|\stdClass[]
	 * @param bool|null $isAssoc
	 * @return ICmdBulkInsert|static
	 */
	public function dataSet(array $documents, bool $isAssoc = null): ICmdBulkInsert
	{
		$this->payload = array_merge($this->payload, $documents);
		return $this;
	}
	
	/**
	 * @param array|\stdClass
	 * @return ICmdBulkInsert|static
	 */
	public function document($document): ICmdBulkInsert
	{
		return $this->data($document);
	}
	
	/**
	 * @param array []|\stdClass[]
	 * @return ICmdBulkInsert|static
	 */
	public function documents(array $documents): ICmdBulkInsert
	{
		return $this->dataSet($documents);
	}
	
	public function execute(): IRawResponse
	{
		if (!$this->db)
			throw new FatalSnuggleException('DB name must be set');
		
		$request = new RawRequest("/{$this->db}/_bulk_docs");
		$request
			->setPost()
			->setBody([
				'docs' => $this->payload
			]);
		
		return $this->connection->request($request);
	}
	
	/**
	 * @return string[]
	 */
	public function queryIDs(): array
	{
		$result = $this->queryJson();
		$ids = [];
		
		foreach ($result as $row)
		{
			$ids[] = $row['id'] ?? null;
		}
		
		return $ids;
	}
	
	/**
	 * @return bool[]
	 */
	public function queryIsSuccessful(): array
	{
		$result = $this->queryJson();
		$results = [];
		
		foreach ($result as $row)
		{
			$results[] = (($row['ok'] ?? false) === true) ;
		}
		
		return $results;
	}
	
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array
	{
		$result = $this->queryJson();
		$docs = [];
		
		for ($index = 0; $index < count($this->payload); $index++)
		{
			if (!isset($result[$index]))
				throw new HttpException($result, 'Malformed response from CouchDB');
			
			$itemData = $result[$index];
			$payload = (array)$this->payload[$index];
			
			$doc = new Doc();
				
			if (($payload['_deleted'] ?? false) && ($itemData['ok'] ?? false))
				$doc->IsDeleted = true;
			
			$doc->ID	= $itemData['id'] ?? null;
			$doc->Rev	= $itemData['rev'] ?? null;
			$doc->Data	= SingleDocParser::parseData($payload);
			
			$docs[] = $doc;
		}
		
		return $docs;
	}
}