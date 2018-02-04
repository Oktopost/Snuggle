<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\ICommand;


interface ISingleDoc extends ICommand
{
	/**
	 * @param string $db
	 * @return ISingleDoc|static
	 */
	public function from(string $db): ISingleDoc;
	
	/**
	 * @param string $rev
	 * @return ISingleDoc|static
	 */
	public function rev(string $rev): ISingleDoc;
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID. 
	 * @return ISingleDoc|static
	 */
	public function doc(string $target, ?string $id = null): ISingleDoc;
}