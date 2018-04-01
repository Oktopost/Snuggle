<?php
namespace Snuggle\Core;


use Objection\LiteSetup;
use Objection\LiteObject;

use Snuggle\Base\Core\IMappedObject;
use Snuggle\Core\Document\Meta;
use Structura\Arrays;


/**
 * @property string			$ID
 * @property string|null	$Rev
 * @property bool			$IsDeleted
 * @property array|null		$Data
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
			'Data'		=> LiteSetup::createArray(null),
			'Meta'		=> LiteSetup::createInstanceOf(Meta::class)
		];
	}
	
	
	public function __construct()
	{
		parent::__construct();
		$this->Meta = new Meta();
	}
	
	
	/**
	 * @param string|array $field
	 * @param mixed $default
	 * @return mixed
	 */
	public function getKey($field, $default = null, bool $scalarOnly = true)
	{
		if ($field == '_id')
		{
			return $this->ID;
		}
		else if ($field == '_rev')
		{
			return $this->Rev;
		}
		else
		{
			$fields = Arrays::toArray($field);
			$value = $this->Data;
			
			foreach ($fields as $field)
			{
				if (is_null($value) || !is_array($value) || !isset($value[$field]))
					return $default;
				
				$value = $value[$field];
			}
			
			if ($scalarOnly && !is_scalar($value))
				return $default;
			
			return $value;
		}
	}
}