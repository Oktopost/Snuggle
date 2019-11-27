<?php
namespace Snuggle\Base\Conflict\Resolvers\Generic;


interface ISimpleResolver
{
	public function ignoreConflict(): void;
	public function overrideConflict(): void;
	public function failOnConflict(): void;
}