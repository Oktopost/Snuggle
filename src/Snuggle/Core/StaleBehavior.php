<?php
namespace Snuggle\Core;


use Traitor\TEnum;


class StaleBehavior
{
	use TEnum;
	
	
	public const OK				= 'ok';
	public const UPDATE_AFTER	= 'update_after';
}