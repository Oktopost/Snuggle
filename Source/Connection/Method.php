<?php
namespace Snuggle\Connection;


use Traitor\TEnum;


class Method
{
	use TEnum;
	
	
	public const GET	= 'GET';
	public const PUT	= 'PUT';
	public const HEAD	= 'HEAD';
	public const POST	= 'POST';
	public const DELETE	= 'DELETE';
}