<?php
namespace Snuggle\Base\Commands;


interface IReadWriteOperation extends IReadOperation, IWriteOperation
{
	/**
	 * @param int $read
	 * @param int $write
	 * @return static
	 */
	public function quorum(int $read, int $write);
}