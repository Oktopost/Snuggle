<?php
namespace Snuggle\Base\Commands;


interface IDocCommand
{
	/**
	 * @param string $db
	 * @return IRevCommand|static
	 */
	public function from(string $db): IRevCommand;
	
	/**
	 * @deprecated 
	 * @param string $db
	 * @return IRevCommand|static
	 */
	public function db(string $db): IRevCommand;
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID. 
	 * @return IRevCommand|static
	 */
	public function doc(string $target, ?string $id = null): IRevCommand;
}