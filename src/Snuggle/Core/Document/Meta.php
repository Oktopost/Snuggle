<?php
namespace Snuggle\Core\Document;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string $LocalSeq
 */
class Meta extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'LocalSeq'	=> LiteSetup::createString(null)
		];
	}
}