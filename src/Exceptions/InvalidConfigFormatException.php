<?php
namespace Snuggle\Exceptions;


use Snuggle\Config\ConnectionConfig;


class InvalidConfigFormatException extends FatalSnuggleException
{
	public function __construct()
	{
		parent::__construct('The element provided is not a valid config object. ' . 
			'Element must be a class name, an interface name, a ' . ConnectionConfig::class . 
			' instance  or an array.');
	}
}