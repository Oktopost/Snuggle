<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdServer;

use Snuggle\Connection\Method;
use Snuggle\Core\Server\Index;
use Snuggle\Core\Server\ActiveTask;


class CmdServer implements ICmdServer
{
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function databases(): array
	{
		return $this->connection->request('/_all_dbs')->getJsonBody();
	}
	
	/**
	 * @param string|null $type
	 * @return ActiveTask[]
	 */
	public function activeTasks(?string $type = null): array
	{
		$result = $this->connection->request('/_active_tasks')->getJsonBody();
		$data = [];
		
		foreach ($result as $record)
		{
			$activityType = $record['type'] ?? null;
			
			if ($type && $type != $activityType)
			{
				continue;
			}
			
			$activity = new ActiveTask();
			
			$activity->Database			= $record['database'] ?? null;
			$activity->DesignDocument	= $record['design_document'] ?? null;
			$activity->PID				= $record['pid'] ?? null;
			$activity->UpdatedOn		= isset($record['pid']) ? date('Y-m-d H:i:s', $record['updated_on']) : null;
			$activity->StartedOn		= isset($record['pid']) ? date('Y-m-d H:i:s', $record['started_on']) : null; 
			$activity->Type				= $record['type'] ?? null;
			$activity->Progress			= isset($record['progress']) ? (((float)($record['progress'])) / 100.0) : null;  
			$activity->OriginalRecord	= $record;
			
			$data[] = $activity;
		}
		
		return $data;
	}
	
	public function info(): Index
	{
		$result = $this->connection->request('/');
		
		$result = $result->getBody()->getJson(true);
		$info = new Index();
		
		$info->UUID				= $result['uuid'] ?? '';
		$info->Version			= $result['version'] ?? '';
		$info->Vendor->Name		= $result['vendor']['name'] ?? '';
		$info->Vendor->Version	= $result['vendor']['version'] ?? '';
		
		return $info;
	}
	
	public function UUIDs(int $count = 20): array
	{
		$result = $this->connection->request('/_uuids', Method::GET, ['count' => $count]);
		$result = $result->getJsonBody();
		return $result['uuids'];
	}
}