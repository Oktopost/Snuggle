<?php
namespace Snuggle\Commands\Abstraction;


use Snuggle\Exceptions\SnuggleException;


trait TQueryRevision
{
	/**
	 * Return the ETag of the inserted document.
	 * @return string
	 */
	public function queryRevision(): string
	{
		$tag = $this->queryETag();
		
		if (is_null($tag))
			throw new SnuggleException('No ETag returned for new object');
		
		$tag = jsondecode($tag);
		
		if (is_null($tag))
			throw new SnuggleException('Malformed ETag for new object');
		
		return $tag;
	}
}