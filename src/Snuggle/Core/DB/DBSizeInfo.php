<?php
namespace Snuggle\Core\DB;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property int $File
 * @property int $Active
 * @property int $External
 */
class DBSizeInfo extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'File'		=> LiteSetup::createInt(),
			'Active'	=> LiteSetup::createInt(),
			'External'	=> LiteSetup::createInt()
		];
	}
}