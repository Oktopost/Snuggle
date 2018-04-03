<?php
namespace Snuggle\Core\Lists;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\Doc;


/**
 * @property string	$DocID
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
			'DocID'	=> LiteSetup::createString(),
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