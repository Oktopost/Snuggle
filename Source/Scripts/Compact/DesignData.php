<?php
namespace Snuggle\Scripts\Compact;


use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string $DB
 * @property string $Name
 * @property float	$Disk
 * @property float	$Data
 * @property bool	$IsCompacted
 * @property bool	$IsLoaded
 */
class DesignData extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'DB'			=> LiteSetup::createString(),
			'Name'			=> LiteSetup::createString(),
			'Disk'			=> LiteSetup::createInt(),
			'Data'			=> LiteSetup::createInt(),
			'IsCompacted'	=> LiteSetup::createBool(false),
			'IsLoaded'		=> LiteSetup::createBool(false)
		];
	}
	
	
	public function getDiskToDataRatio(): float
	{
		if ($this->Data == 0)
			return 1;
		
		return ($this->Disk == 0 ? 0.0 : $this->Disk / $this->Data);
	}
}