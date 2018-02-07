<?php
namespace Snuggle\Base\Conflict\Resolvers\Generic;


interface IMergeResolver
{
	public function mergeNewOnConflict(): void;
	public function mergeOverOnConflict(): void;
}