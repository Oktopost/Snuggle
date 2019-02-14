<?php
namespace Snuggle\Core\DB;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\TMappedObject;
use Snuggle\Base\Core\IMappedObject;


/**
 * @property string			$Name
 * @property string|null	$Signature
 * @property int			$DataSize
 * @property int			$DiskSize
 * @property string			$Language
 * @property int			$PurgeSeq
 * @property string			$UpdateSeq
 * @property bool			$IsUpdaterRunning
 * @property bool			$WaitingClients
 * @property bool			$IsWaitingCommit
 */
class DDocInfo extends LiteObject implements IMappedObject
{
	use TMappedObject;
	
	
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Name'				=> LiteSetup::createString(),
			'Signature'			=> LiteSetup::createString(null),
			'DataSize'			=> LiteSetup::createInt(),
			'DiskSize'			=> LiteSetup::createInt(),
			'Language'			=> LiteSetup::createString(),
			'PurgeSeq'			=> LiteSetup::createInt(0),
			'UpdateSeq'			=> LiteSetup::createString(),
        	'IsUpdaterRunning'	=> LiteSetup::createBool(),
        	'WaitingClients'	=> LiteSetup::createInt(0),
        	'IsWaitingCommit'	=> LiteSetup::createBool(),
        	'IsCompactRunning'	=> LiteSetup::createBool()
		];
	}
	
	
	public function getDataToDiskSizeRatio(): float
	{
		if ($this->DiskSize == 0)
			return 0;
		
		return $this->DataSize / $this->DiskSize;
	}
}