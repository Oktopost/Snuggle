<?php
namespace Snuggle\Core\DB;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property bool	$IsClustered
 * @property int	$Replicas
 * @property int	$Shards
 * @property int	$ReadQuorum
 * @property int	$WriteQuorum
 */
class DBClusterInfo extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'IsClustered'	=> LiteSetup::createBool(false),
			'Replicas'		=> LiteSetup::createInt(0),
			'Shards'		=> LiteSetup::createInt(0),
			'ReadQuorum'	=> LiteSetup::createInt(0),
			'WriteQuorum'	=> LiteSetup::createInt(0)
		];
	}
}