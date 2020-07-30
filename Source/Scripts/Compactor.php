<?php
namespace Snuggle\Scripts;


use Snuggle\CouchDB;
use Snuggle\Scripts\Compact\DBData;
use Snuggle\Scripts\Compact\CompactDAO;
use Snuggle\Scripts\Compact\DBNameFilter;
use Snuggle\Scripts\Compact\DesignData;
use Snuggle\Scripts\Compact\InstanceData;
use Snuggle\Exceptions\Http\NotFoundException;


class Compactor
{
	private $connectionSet = false;
	private $filters = ['*', '!_*'];
	
	private $settingsDB;
	
	/** @var CouchDB */
	private $couchDB;
	
	
	/**
	 * @return DBData[]
	 */
	private function loadDBs(): array 
	{
		$result = [];
		
		$dbs = $this->couchDB->connector()
			->server()
			->databases();
		
		$dbs = DBNameFilter::filter($dbs, $this->filters);
		
		foreach ($dbs as $db)
		{
			$dbData = new DBData();
			$dbData->Name = $db;
			$result[] = $dbData;
		}
		
		return $result;
	}
	
	/**
	 * @param DBData[] $dbs
	 */
	private function loadDB(array $dbs): void
	{
		$db = $dbs[array_rand($dbs)];
		
		$designDocs = $this->couchDB->connector()
			->db()
			->designDocs($db->Name);
		
		$db->IsLoaded = true;
		
		foreach ($designDocs as $ddoc)
		{
			$object = new DesignData();
			
			$object->DB		= $db->Name;
			$object->Name	= $ddoc;
			
			$db->Designs[] = $object;
		}
	}
	
	/**
	 * @param DesignData[] $designs
	 */
	private function loadDesign(array $designs): void
	{
		$design = $designs[array_rand($designs)];
		
		$info = $this->couchDB->connector()
			->db()
			->designDocInfo($design->DB, $design->Name);
		
		$design->IsLoaded = true;
		$design->Disk = $info->DiskSize;
		$design->Data = $info->DataSize;
	}
	
	/**
	 * @param DesignData[] $designs
	 */
	private function compactDesign(array $designs): void
	{
		$design = $designs[array_rand($designs)];
		
		$tasks = $this->couchDB->connector()
			->server()
			->activeTasks('view_compaction');
		
		if ($tasks)
			return;
		
		try
		{
			$this->couchDB->connector()->db()->compact($design->DB, $design->Name);
		}
		// Skip deleted views.
		catch (NotFoundException $e) {}
		
		$design->IsCompacted = true;
	}
	
	
	public function __construct()
	{
		$this->couchDB = new CouchDB();
	}
	
	
	public function setConnection(array $config, string $settingsDB = 'snuggle_settings'): Compactor
	{
		$this->connectionSet = true;
		$this->settingsDB = $settingsDB;
		$this->couchDB->config()->addConnection('main', $config);
		return $this;
	}
	
	
	public function setDBFilters($filters): Compactor
	{
		$filters[] = '!_*';
		$this->filters = $filters;
		return $this;
	}
	
	
	/**
	 * @param float $minRatio
	 * @return bool True if this script should run again today, false if all databases/views for today were compacted.
	 */
	public function run(float $minRatio = 3.5): bool
	{
		$dao = new CompactDAO($this->couchDB->connector(), $this->settingsDB);
		$data = $dao->getDataObject();
		
		if (!$data)
		{
			$data = new InstanceData();
			$data->DBs = $this->loadDBs();
		}
		
		$unloadedDBs = $data->getUnloadedDBs();
		$unloadedDesigns = $data->getUnloadedDesigns();
		$uncompactedDesigns = $data->getUncompactedDesigns($minRatio);
		
		if (!$unloadedDBs && !$unloadedDesigns && !$uncompactedDesigns)
		{
			return false;
		}
		
		if ($unloadedDBs)
		{
			$this->loadDB($unloadedDBs);
		}
		
		if ($unloadedDesigns)
		{
			$this->loadDesign($unloadedDesigns);
		}
		
		if ($uncompactedDesigns)
		{
			$this->compactDesign($uncompactedDesigns);
		}
		
		$dao->saveDataObject($data);
		
		return true;
	}
}