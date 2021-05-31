<?php
namespace Snuggle\Base\Commands;


interface IReadOperation
{
	/**
	 * @param int $quorum
	 * @return static
	 */
	public function readQuorum(int $quorum);
}