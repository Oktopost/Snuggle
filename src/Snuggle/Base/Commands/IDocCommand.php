<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\ICommand;


interface IDocCommand extends ICommand
{
	/**
	 * @param string $db
	 * @return IDocCommand|static
	 */
	public function from(string $db): IDocCommand;
	
	/**
	 * @param string $rev
	 * @return IDocCommand|static
	 */
	public function rev(string $rev): IDocCommand;
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID. 
	 * @return IDocCommand|static
	 */
	public function doc(string $target, ?string $id = null): IDocCommand;
}