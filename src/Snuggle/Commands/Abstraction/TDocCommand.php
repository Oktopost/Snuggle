<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Base\Commands\IDocCommand;


trait TDocCommand
{
	private $_db;
	private $_id;
	
	
	public function getDB(): string
	{
		return $this->_db;
	}
	
	public function getDocId(): string
	{
		return $this->_id;
	}
	
	
	/**
	 * @param string $db
	 * @return IDocCommand|static
	 */
	public function from(string $db): IDocCommand
	{
		$this->db = $db;
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
			$this->db = $target;
			$this->id = $id;
		}
		else
		{
			$this->id = $target;
		}
		
		return $this;
	}
}