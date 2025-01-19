<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdChanges;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Core\Lists\ChangeList;
use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Connection\Parsers\SingleDocParser;
use Snuggle\Connection\Parsers\Lists\ChangesListParser;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Exceptions\FatalSnuggleException;


class CmdChanges implements ICmdChanges
{
	use TQuery;
	use TExecuteSafe;
	
	
	private ?string $dbName = null;
	private array $params = [];
	private array $filterParams = [];
	
	
	private IConnection $connection;
	
	
	private function getParams(): array
	{
		$result = [];
		
		foreach ($this->params as $key => $value) 
		{
			$result[$key] = jsonencode($value);
		}
		
		foreach ($this->filterParams as $key => $value) 
		{
			$result[$key] = $value;
		}
		
		return $result;
	}
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function db(string $name): self
	{
		$this->dbName = $name;
		return $this;
	}
	
	public function descending(bool $isDesc = true): self
	{
		$this->params['descending'] = $isDesc;
		return $this;
	}
	
	public function limit(int $limit): self
	{
		$this->params['limit'] = $limit;
		return $this;
	}
	
	public function since(string $id): self
	{
		$this->params['since'] = $id;
		return $this; 
	}
	
	public function style(string $style): self
	{
		$this->params['style'] = $style;
		return $this;
	}
	
	public function view(string $design, string $view): self
	{
		$this->filterParams = [];
		$this->filterParams['filter'] = '_view';
		$this->filterParams['view'] = "$design/$view";
		
		return $this;
	}
	
	public function includeDocs(bool $include = true): self
	{
		$this->params['include_docs'] = $include;
		return $this;
	}
	
	public function execute(): IRawResponse
	{
		if (!$this->dbName)
		{
			throw new FatalSnuggleException('Database name not set. ' .
				'Method `db` must be called before executing the query');
		}
		
		$request = RawRequest::create("{$this->dbName}/_changes", params: $this->getParams());
		
		return $this->connection->request($request);
	}
	
	public function queryList(): ChangeList
	{
		return ChangesListParser::parseResponse($this->execute());
	}
	
	public function queryRows(): array
	{
		return $this->queryList()->Rows;
	}
	
	public function queryDocs(): array
	{
		$response = (clone $this)
			->includeDocs()
			->queryJson();
		
		$rows = $response['results'];
		$result = [];
		
		foreach ($rows as $row)
		{
			if (!isset($row['id']) || isset($row['error']) || !isset($row['doc']))
				continue;
			
			$result[] = SingleDocParser::parseData($row['doc']);
		}
		
		return $result;
	}
}