<?php
namespace Snuggle\Scripts\Compact;


use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string 		$Name
 * @property bool			$IsCompacted
 * @property bool			$IsLoaded
 * @property int|null		$Size
 * @property DesignData[]	$Designs
 */
class DBData extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Name'			=> LiteSetup::createString(),
			'IsLoaded'		=> LiteSetup::createBool(false),
			'IsCompacted'	=> LiteSetup::createBool(false),
			'Size'			=> LiteSetup::createInt(null),
			'Designs'		=> LiteSetup::createInstanceArray(DesignData::class)
		];
	}
	
	
	/**
	 * @return DesignData[]
	 */
	public function getUnloadedDesigns(): array 
	{
		$data = [];
		
		if (!$this->IsLoaded)
			return [];
		
		foreach ($this->Designs as $design)
		{
			if (!$design->IsLoaded)
			{
				$data[] = $design;
			}
		}
		
		return $data;
	}
	
	public function getUncompacted(float $minRatio): array
	{
		$data = [];
		
		if (!$this->IsLoaded)
			return [];
		
		foreach ($this->Designs as $design)
		{
			if ($design->IsLoaded && !$design->IsCompacted && $design->getDiskToDataRatio() >= $minRatio)
			{
				$data[] = $design;
			}
		}
		
		return $data;
	}
}