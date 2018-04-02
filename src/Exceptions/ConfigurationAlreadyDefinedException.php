<?php
namespace Snuggle\Exceptions;


class ConfigurationAlreadyDefinedException extends FatalSnuggleException
{
	private $name;
	
	
	public function __construct(string $name)
	{
		parent::__construct("Trying to redefined connection config '$name'");
	}
	
	
	public function getName(): string
	{
		return $this->name;
	}
}