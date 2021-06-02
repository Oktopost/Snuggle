<?php
namespace Snuggle\Base\Commands;


interface IWriteOperation
{
	/**
	 * @param int $quorum
	 * @return static
	 */
	public function quorumWrite(int $quorum);
}