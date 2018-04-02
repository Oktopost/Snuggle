<?php
namespace Snuggle\Base\Commands;


interface IRevCommand extends IDocCommand
{
	/**
	 * @param string $rev
	 * @return IRevCommand|static
	 */
	public function rev(string $rev): IRevCommand;
}