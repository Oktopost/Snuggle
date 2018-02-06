<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IDocCommand;


trait TDocCommand
{
	private $_db;
	private $_id;
	
	
	protected function uri(): string
	{
		return $this->_db . '/' . $this->_id;
	}
	
	
	public function getDB(): string
	{
		return $this->_db;
	}
	
	public function getDocID(): string
	{
		return $this->_id;
	}
	
	
	/**
	 * @param string $db
	 * @return IDocCommand|static
	 */
	public function from(string $db): IDocCommand
	{
		$this->_db = $db;
		return $this;
	}
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID.
	 * @return IDocCommand|static
	 */
	public function doc(string $target, ?string $id = null): IDocCommand
	{
		if ($id)
		{
			$this->_db = $target;
			$this->_id = $id;
		}
		else
		{
			$this->_id = $target;
		}
		
		return $this;
	}
}