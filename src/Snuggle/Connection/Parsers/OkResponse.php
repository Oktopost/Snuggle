<?php
namespace Snuggle\Connection\Parsers;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\UnexpectedHttpResponseException;
use Traitor\TStaticClass;


/**
 * Expect {"ok":true} response from server.
 */
class OkResponse
{
	use TStaticClass;
	
	
	public static function parse(IRawResponse $response)
	{
		$res = $response->getJsonBody(true);
		
		if (!$res || !is_array($res) || ($res['ok'] ?? false) !== true)
		{
			throw new UnexpectedHttpResponseException($response, $response->request(), 'Expecting {"ok":true}');
		}
	}
}