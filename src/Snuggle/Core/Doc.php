<?php
namespace Snuggle\Core;


use Objection\LiteObject;
use Snuggle\Base\Core\IMappedObject;


class Doc extends LiteObject implements IMappedObject
{
	use TMappedObject;
	
	
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [];
	}
}