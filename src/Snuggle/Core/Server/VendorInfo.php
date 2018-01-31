<?php
namespace Snuggle\Core\Server;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string $Name
 * @property string $Version
 */
class VendorInfo extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Name'		=> LiteSetup::createString(),
			'Version'	=> LiteSetup::createString(), 
		];
	}
}