<?php
namespace Snuggle\Core\Lists;


use Snuggle\Core\Doc;
use Snuggle\Core\Document\RevisionInfo;

use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string         $DocID
 * @property string         $Seq
 * @property RevisionInfo[] $Changes
 * @property Doc|null       $Doc
 */
class ChangeRow extends LiteObject
{
	protected function _setup(): array
	{
		return [
			'DocID'   => LiteSetup::createString(),
			'Seq'     => LiteSetup::createString(),
			'Changes' => LiteSetup::createInstanceArray(RevisionInfo::class),
			'Doc'     => LiteSetup::createInstanceOf(Doc::class)
		];
	}


	public function hasDoc(): bool
	{
		return (bool)$this->Doc;
	}
}
