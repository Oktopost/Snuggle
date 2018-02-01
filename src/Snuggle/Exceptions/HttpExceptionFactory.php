<?php
namespace Snuggle\Exceptions;


use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Exceptions\Http\ConflictException;
use Snuggle\Exceptions\Http\NotFoundException;
use Snuggle\Exceptions\Http\ForbiddenException;
use Snuggle\Exceptions\Http\BadRequestException;
use Snuggle\Exceptions\Http\ServerErrorException;
use Snuggle\Exceptions\Http\UnauthorizedException;
use Snuggle\Exceptions\Http\MethodNotAllowedException;
use Snuggle\Exceptions\Http\PreconditionFailedException;
use Snuggle\Exceptions\Http\UnexpectedResponseException;

use Traitor\TStaticClass;


class HttpExceptionFactory
{
	use TStaticClass;
	
	
	public static function getException(
		IRawRequest $request, 
		IRawResponse $response, 
		?string $message = null
	): HttpException
	{
		switch ($response->getCode())
		{
			case 404:
				return new NotFoundException($response, $request, $message);
				
			case 409:
				return new ConflictException($response, $request, $message);
				
			case 400:
				return new BadRequestException($response, $request, $message);
				
			case 401:
				return new UnauthorizedException($response, $request, $message);
				
			case 403:
				return new ForbiddenException($response, $request, $message);
				
			case 405:
				return new MethodNotAllowedException($response, $request, $message);
			
			case 412:
				return new PreconditionFailedException($response, $request, $message);
			
			case 500:
				return new ServerErrorException($response, $request, $message);
				
			default:
				return new UnexpectedResponseException($response, $request, $message);
		}
	}
}