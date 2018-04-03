<?php
namespace Snuggle\Core\Lists;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\Doc;


/**
 * @property string	$ID
 * @property mixed	$Key
 * @property mixed	$Value
 * @property Doc	$Doc
 */
class ViewRow extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'ID'	=> LiteSetup::createString(),
			'Key'	=> LiteSetup::createMixed(),
			'Value'	=> LiteSetup::createMixed(),
			'Doc'	=> LiteSetup::createInstanceOf(Doc::class)
		];
	}
	
	
	public function hasDoc(): bool
	{
		return (bool)$this->Doc;
	}
}