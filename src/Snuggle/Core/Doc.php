<?php
namespace Snuggle\Core;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Base\Core\IMappedObject;
use Snuggle\Core\Document\Meta;


/**
 * @property string			$ID
 * @property string|null	$Rev
 * @property bool			$IsDeleted
 * @property array			$Data
 * @property Meta			$Meta
 */
class Doc extends LiteObject implements IMappedObject
{
	use TMappedObject;
	
	
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'ID'		=> LiteSetup::createString(''),
			'Rev'		=> LiteSetup::createString(null),
			'IsDeleted'	=> LiteSetup::createBool(false),
			'Data'		=> LiteSetup::createArray([]),
			'Meta'		=> LiteSetup::createInstanceOf(Meta::class)
		];
	}
	
	
	public function __construct()
	{
		parent::__construct();
		$this->Meta = new Meta();
	}
}