<?php
namespace Snuggle\Core\Lists;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property int			$Pending
 * @property string			$LastSeq
 * @property ChangeRow[]	$Rows
 */
class ChangeList extends LiteObject
{
	protected function _setup(): array
	{
		return [
			'Pending'	=> LiteSetup::createInt(),
			'LastSeq'	=> LiteSetup::createString(),
			'Rows'		=> LiteSetup::createInstanceArray(ChangeRow::class)
		];
	}
	
	
	public function count(): int
	{
		return count($this->Rows);
	}
}