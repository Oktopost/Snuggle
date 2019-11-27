<?php
namespace Snuggle\Exceptions;


class ConfigurationNotFoundException extends FatalSnuggleException
{
	private $name;
	
	
	public function __construct(string $name)
	{
		$this->name = $name;
		parent::__construct("Configuration '$name' was not found");
	}
	
	
	public function getMissingName(): string
	{
		return $this->name;
	}
}