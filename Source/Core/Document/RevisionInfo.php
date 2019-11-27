<?php
namespace Snuggle\Core\Document;


use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string $Rev
 * @property string $Status
 */
class RevisionInfo extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Rev'		=> LiteSetup::createString(),
			'Status'	=> LiteSetup::createString()
		];
	}
	
	
	public function __construct(?string $rev = null, ?string $status = null)
	{
		parent::__construct();
		
		if ($rev)
			$this->Rev = $rev;
		
		if ($status)
			$this->Status = $status;
	}
	
	
	public function isAvailable(): bool
	{
		return $this->Status == 'available';
	}
}