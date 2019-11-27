<?php
namespace Snuggle\Core;


trait TMappedObject
{
	private $_source;
	
	
	public function setSource(array $source): void
	{
		$this->_source = $source;
	}
	
	public function source(): array
	{
		return $this->_source ?: [];
	}
}