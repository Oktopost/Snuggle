<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdChanges;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Core\Lists\ChangeList;
use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Connection\Method;
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


	private IConnection $connection;


	private function setJsonParam(string $key, $value, $unsetValue = null): static
	{
		if ($value === $unsetValue)
		{
			unset($this->params[$key]);
		}
		else
		{
			$this->params[$key] = jsonencode($value);
		}

		return $this;
	}

	private function setFiltersParameter(?array $value = null): static
	{
		unset($this->params['filter']);
		unset($this->params['view']);
		unset($this->params['selector']);

		if ($value)
			$this->params = array_merge($this->params, $value);

		return $this;
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
		return $this->setJsonParam('descending', $isDesc);
	}

	public function limit(int $limit): self
	{
		return $this->setJsonParam('limit', $limit);
	}

	public function since(string $id): self
	{
		return $this->setJsonParam('since', $id);
	}

	public function style(string $style): self
	{
		return $this->setJsonParam('style', $style);
	}

	public function view(?string $design, ?string $view = null): self
	{
		if ($design && $view)
		{
			$filter = [
				'filter' => '_view',
				'view'   => "$design/$view"
			];
		}
		else
		{
			$filter = null;
		}

		return $this->setFiltersParameter($filter);
	}

	public function selector(array $selector): self
	{
		if ($selector)
		{
			$filter = [
				'filter'   => '_selector',
				'selector' => $selector
			];
		}
		else
		{
			$filter = null;
		}

		return $this->setFiltersParameter($filter);
	}

	public function includeDocs(bool $include = true): self
	{
		return $this->setJsonParam('include_docs', $include);
	}

	public function execute(): IRawResponse
	{
		if (!$this->dbName)
		{
			throw new FatalSnuggleException('Database name not set. ' .
				'Method `db` must be called before executing the query');
		}

		$method   = Method::GET;
		$params   = $this->params;
		$selector = [];

		if (isset($params['selector']))
		{
			$method   = Method::POST;
			$selector = $params['selector'];

			unset($params['selector']);
		}

		$request = RawRequest::create("{$this->dbName}/_changes", $method, $params);

		if ($selector)
		{
			$request->setBody(['selector' => $selector]);
		}

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
