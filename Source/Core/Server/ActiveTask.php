<?php
namespace Snuggle\Core\Server;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string $Database
 * @property string $DesignDocument
 * @property string $PID
 * @property string $UpdatedOn
 * @property string $StartedOn
 * @property string $Type
 * @property double	$Progress
 * @property array	$OriginalRecord
 */
class ActiveTask extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Database'			=> LiteSetup::createString(null),
			'DesignDocument'	=> LiteSetup::createString(null),
			'PID'				=> LiteSetup::createString(),
			'UpdatedOn'			=> LiteSetup::createString(),
			'StartedOn'			=> LiteSetup::createString(),
			'Type'				=> LiteSetup::createString(),
			'Progress'			=> LiteSetup::createDouble(),
			'OriginalRecord'	=> LiteSetup::createArray()
		];
	}
}