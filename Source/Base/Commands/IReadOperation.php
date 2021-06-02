<?php
namespace Snuggle\Base\Commands;


interface IReadOperation
{
	/**
	 * @param int $quorum
	 * @return static
	 */
	public function quorumRead(int $quorum);
}