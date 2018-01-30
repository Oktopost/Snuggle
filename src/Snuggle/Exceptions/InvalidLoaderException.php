<?php
namespace Snuggle\Exceptions;


use Snuggle\Config\IConfigLoader;


class InvalidLoaderException extends FatalSnuggleException
{
	public function __construct()
	{
		parent::__construct('The element provided is not a valid config loader. ' . 
			'Element must be a class name, an interface name or an instance that implements ' . 
			IConfigLoader::class . 'interface.');
	}
}