<?php
namespace Snuggle\Scripts\Compact;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property bool		$Date
 * @property bool		$Created
 * @property bool		$IsCompacted
 * @property bool		$IsLoaded
 * @property DBData[]	$DBs
 */
class InstanceData extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Date'			=> LiteSetup::createString(date('Y-m-d')),
			'Created'		=> LiteSetup::createString(date('Y-m-d H:i:s')),
			'IsCompacted'	=> LiteSetup::createBool(false),
			'IsLoaded'		=> LiteSetup::createBool(false),
			'DBs'			=> LiteSetup::createInstanceArray(DBData::class)
		];
	}
	
	
	/**
	 * @return DBData[]
	 */
	public function getUnloadedDBs(): array 
	{
		$data = [];
		
		foreach ($this->DBs as $db)
		{
			if (!$db->IsLoaded)
			{
				$data[] = $db;
			}
		}
		
		return $data;
	}
	/**
	 * @return DesignData[]
	 */
	public function getUnloadedDesigns(): array 
	{
		$data = [];
		
		foreach ($this->DBs as $db)
		{
			$data = array_merge($data, $db->getUnloadedDesigns());
		}
		
		return $data;
	}
	
	public function getUncompactedDesigns(float $minRatio): array
	{
		$data = [];
		
		foreach ($this->DBs as $db)
		{
			$data = array_merge($data, $db->getUncompacted($minRatio));
		}
		
		return $data;
	}
	
	public function getUncompactedDBs(): array
	{
		$data = [];
		
		foreach ($this->DBs as $db)
		{
			if (!$db->IsCompacted)
			{
				$data[] = $db;
			}
		}
		
		return $data;
	}
}