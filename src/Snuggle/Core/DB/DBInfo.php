<?php
namespace Snuggle\Core\DB;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\TMappedObject;
use Snuggle\Base\Core\IMappedObject;


/**
 * @property string			$UUID
 * @property string			$Name
 * @property bool			$CompactRunning
 * @property int			$DiskFormatVersion
 * @property int			$DocsCount
 * @property int			$DeletedDocsCount
 * @property string			$UpdateSeq
 * @property int			$PurgeSeq
 * @property DBSizeInfo		$Sizes
 * @property DBClusterInfo	$Cluster
 */
class DBInfo extends LiteObject implements IMappedObject
{
	use TMappedObject;
	
	
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'UUID'				=> LiteSetup::createString(),
			'Name'				=> LiteSetup::createString(),
			'CompactRunning'	=> LiteSetup::createBool(false),
			'DiskFormatVersion'	=> LiteSetup::createInt(-1),
			'DocsCount'			=> LiteSetup::createInt(0),
			'DeletedDocsCount'	=> LiteSetup::createInt(0),
			'UpdateSeq'			=> LiteSetup::createString(),
			'PurgeSeq'			=> LiteSetup::createInt(0),
			'Sizes'				=> LiteSetup::createInstanceOf(DBSizeInfo::class),
			'Cluster'			=> LiteSetup::createInstanceOf(DBClusterInfo::class)
		];
	}
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->Sizes	= new DBSizeInfo();
		$this->Cluster	= new DBClusterInfo();       
	}
}