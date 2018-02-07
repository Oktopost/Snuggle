<?php
namespace Snuggle\Base\Commands;


interface IQueryRevision
{
	/**
	 * Return the ETag of the inserted document.
	 * @return string
	 */
	public function queryRevision(): string;
}