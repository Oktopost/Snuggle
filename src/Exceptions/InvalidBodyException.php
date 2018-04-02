<?php
namespace Snuggle\Exceptions;


class InvalidBodyException extends FatalSnuggleException
{
	public function __construct()
	{
		parent::__construct('The data object passed as the body of the request, is not of a valid type');
	}
}