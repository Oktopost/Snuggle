<?php
namespace Snuggle\Core\Document;


use Structura\Map;
use Structura\Set;
use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string|null		$LocalSeq
 * @property int|null			$RevisionsStart
 * @property Set|null 			$RevisionLocalIDs
 * @property Map|RevisionInfo[]	$Revisions
 */
class Meta extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'LocalSeq'			=> LiteSetup::createString(null),
			'RevisionsStart'	=> LiteSetup::createInt(null),
			'RevisionLocalIDs'	=> LiteSetup::createInstanceOf(Set::class),
			'Revisions'			=> LiteSetup::createInstanceOf(Map::class)
		];
	}
	
	
	public function setRevisions(int $start, array $ids): void
	{
		$this->RevisionsStart = $start;
		$this->RevisionLocalIDs = new Set($ids);
	}
	
	public function getRevisionsCount(): ?int 
	{
		return $this->Revisions ? 
			$this->Revisions->count() : 
			null; 
	}
}