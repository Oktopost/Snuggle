<?php
namespace Snuggle\Core\Lists;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Core\Doc;


/**
 * @property int|null		$Total
 * @property int			$Offset
 * @property string|null	$UpdateSeq
 * @property Doc[]			$Docs
 */
class ViewList extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Total'		=> LiteSetup::createInt(),
			'Offset'	=> LiteSetup::createInt(),
			'UpdateSeq'	=> LiteSetup::createString(null),
			'Docs'		=> LiteSetup::createInstanceArray(Doc::class)
		];
	}
	
	
	public function count(): int
	{
		return count($this->Docs);
	}
}