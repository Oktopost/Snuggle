<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\IRefreshView;
use Snuggle\Commands\CmdBulkGet;
use Snuggle\Commands\CmdGet;
use Snuggle\Connection\Method;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Core\StaleBehavior;


trait TRefreshView
{
	/** @var IConnection */
	private $_refreshConnection;
	
	private $_db;
	
	private $defaultRefreshView = 'refresh_view';
	
	private $operationsCount = 0;
	private $refreshProbability = 0;
	private $refreshDocsToViews = [];
	
	
	private function needToRefresh(int $itemsCount): bool
	{
		if ((!$this->operationsCount && !$this->refreshProbability) ||
			!$this->refreshDocsToViews ||
			($this->operationsCount && !$itemsCount))
		{
			return false;
		}
		
		$rand = (float)rand()/(float)getrandmax();
		$probability = $this->operationsCount ? ($itemsCount / $this->operationsCount) : $this->refreshProbability;
		
		return $rand < $probability;
	}
	
	private function refreshViews(int $itemsCount = 1): void
	{
		if (!$this->needToRefresh($itemsCount))
			return;
		
		foreach ($this->refreshDocsToViews as $designDoc => $view)
		{
			$command = new CmdBulkGet($this->_refreshConnection);
			$command
				->from($this->_db)
				->view($designDoc, $view)
				->stale(StaleBehavior::UPDATE_AFTER)
				->includeDocs(false)
				->limit(0)
				->execute();
		}
	}
	
	private function setRefreshDB(string $db): void
	{
		$this->_db = $db;
	}
	
	private function setRefreshConnection(IConnection $connection): void
	{
		$this->_refreshConnection = $connection;
	}
	
	
	/**
	 * @param int $operationsCount
	 * @return IRefreshView|static
	 */
	public function setRefreshOperations(int $operationsCount): IRefreshView
	{
		$this->operationsCount = $operationsCount;
		return $this;
	}
	
	/**
	 * @param float $probability
	 * @return IRefreshView|static
	 */
	public function setRefreshProbability(float $probability): IRefreshView
	{
		$this->refreshProbability = $probability;
		return $this;
	}

	/**
	 * @param string $designDocName
	 * @param string $refreshView
	 * @return IRefreshView|static
	 */
	public function setRefreshDoc(string $designDocName, ?string $refreshView = null): IRefreshView
	{
		$this->refreshDocsToViews[$designDocName] = $refreshView ?: $this->defaultRefreshView;
		return $this;
	}

	/**
	 * @param array $designDocNames
	 * @param string $refreshView
	 * @return IRefreshView|static
	 */
	public function setRefreshDocs(array $designDocNames, ?string $refreshView = null): IRefreshView
	{
		foreach ($designDocNames as $designDocName)
		{
			$this->setRefreshDoc($designDocName, $refreshView);
		}
		
		return $this;
	}
	
	/**
	 * @param array $designDocNamesToViews
	 * @return IRefreshView|static
	 */
	public function setRefreshDocViews(array $designDocNamesToViews): IRefreshView
	{
		foreach ($designDocNamesToViews as $designDocName => $viewName)
		{
			if (!is_string($designDocName))
				continue;
			
			$this->setRefreshDoc($designDocName, $viewName);
		}
		
		return $this;
	}
}