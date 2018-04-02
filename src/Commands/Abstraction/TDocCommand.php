<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IRevCommand;
use Snuggle\Exceptions\SnuggleException;


trait TDocCommand
{
	private $_db = null;
	private $_id = null;
	
	
	private function requireDBAndDocID(): void
	{
		if (!$this->_db || !$this->_id)
			throw new SnuggleException('DB name and document ID must be set');
	}
	
	
	protected function uri(): string
	{
		if ($this->_id)
			return $this->_db . '/' . $this->_id;
		
		return $this->_db;
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
	 * @return IRevCommand|static
	 */
	public function db(string $db): IRevCommand
	{
		$this->_db = $db;
		return $this;
	}
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID.
	 * @return IRevCommand|static
	 */
	public function doc(string $target, ?string $id = null): IRevCommand
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