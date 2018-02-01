<?php
namespace Snuggle\Exceptions;


use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\Http\NotFoundException;
use Snuggle\Exceptions\Http\ForbiddenException;
use Snuggle\Exceptions\Http\BadRequestException;
use Snuggle\Exceptions\Http\ServerErrorException;
use Snuggle\Exceptions\Http\UnauthorizedException;
use Snuggle\Exceptions\Http\MethodNotAllowedException;
use Snuggle\Exceptions\Http\PreconditionFailedException;
use Snuggle\Exceptions\Http\UnexpectedHttpResponseException;

use Traitor\TStaticClass;


class HttpExceptionFactory
{
	use TStaticClass;
	
	
	public static function getException(IRawResponse $response, ?string $message = null): HttpException
	{
		switch ($response->getCode())
		{
			case 404:
				return new NotFoundException($response, $message);
				
			case 409:
				return new ConflictException($response, $message);
				
			case 400:
				return new BadRequestException($response, $message);
				
			case 401:
				return new UnauthorizedException($response, $message);
				
			case 403:
				return new ForbiddenException($response, $message);
				
			case 405:
				return new MethodNotAllowedException($response, $message);
			
			case 412:
				return new PreconditionFailedException($response, $message);
			
			case 500:
				return new ServerErrorException($response, $message);
				
			default:
				return new UnexpectedHttpResponseException($response, $message);
		}
	}
}