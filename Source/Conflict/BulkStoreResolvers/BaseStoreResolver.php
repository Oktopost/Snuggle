<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Base\Commands\ICmdBulkGet;
use Snuggle\Base\IConnector;
use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\Store\IBulkStoreResult;
use Snuggle\Base\Conflict\Resolvers\IBulkStoreResolution;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Core\Doc;
use Snuggle\Exceptions\Http\ConflictException;

use Structura\Map;


abstract class BaseStoreResolver implements IBulkStoreResolution
{
	private string	$from = '';
	private bool	$forceUpdateUnmodified = false;
	private ?int	$readQuorum = null;
	
	private ?IConnection		$connection = null;
	private ?IBulkStoreResult	$store = null;
	private ?IConnector			$connector = null;
	
	
	protected function isForceUpdateUnmodified(): bool
	{
		return $this->forceUpdateUnmodified;
	}
	
	protected function getConnection(): IConnection
	{
		return $this->connection;
	}
	
	protected function getStore(): IBulkStoreResult
	{
		return $this->store;
	}
	
	protected function getConnector(): IConnector
	{
		return $this->connector;
	}
	
	protected function db(): string 
	{
		return $this->from;
	}
	
	protected function getPendingIds(): array
	{
		return array_column($this->getStore()->Pending, '_id');
	}
	
	protected function getReadQuorum(): ?int
	{
		return $this->readQuorum;
	}

	protected function getStoredDocumentsQuery(): ICmdBulkGet
	{
		$readQuorum = $this->getReadQuorum();
		$query = $this->getConnector()->getAll()
			->from($this->db())
			->keys($this->getPendingIds())
			->includeDocs();
		
		if ($readQuorum)
		{
			$query->readQuorum($readQuorum);
		}
		
		return $query;
	}
	
	/**
	 * @return Doc[]|Map
	 */
	protected function getStoredDocuments(): Map
	{
		return $this
			->getStoredDocumentsQuery()
			->queryDocsMap();
	}
	
	
	protected abstract function doResolve(): void;
	
	
	public function setReadQuorum(?int $read): void
	{
		$this->readQuorum = $read;
	}
	
	public function forceUpdateUnmodified(bool $force = false): void
	{
		$this->forceUpdateUnmodified = $force;
	}
	
	public function setConnection(IConnector $connector, IConnection $connection): void
	{
		$this->connector = $connector;
		$this->connection = $connection;
	}
	
	public function from(string $db): void
	{
		$this->from = $db;
	}
	
	public function setStore(IBulkStoreResult $store): void
	{
		$this->store = $store;
	}
	
	public function resolve(ConflictException $exception, IRawResponse $response): bool
	{
		$store = $this->getStore();
		
		foreach ($store->Pending as $index => $data)
		{
			if (!key_exists('_id', $data))
				$store->removePendingAt($index);
		}
		
		if ($store->hasPending())
		{
			$this->doResolve();
			return $store->hasPending();
		}
		else
		{
			return false;
		}
	}
}