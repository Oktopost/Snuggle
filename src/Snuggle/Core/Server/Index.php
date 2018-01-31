<?php
namespace Snuggle\Core\Server;


use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string $UUID
 * @property string $Version
 * @property VendorInfo $Vendor
 */
class Index extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'UUID'		=> LiteSetup::createString(),
			'Version'	=> LiteSetup::createString(),
			'Vendor'	=> LiteSetup::createInstanceOf(VendorInfo::class)
		];
	}
	
	
	public function __construct()
	{
		parent::__construct();
		$this->Vendor = new VendorInfo();
	}
}