<?php
namespace Snuggle\Connection\Parsers;


use Snuggle\Core\Doc;
use Snuggle\Base\Connection\Response\IRawResponse;

use Traitor\TStaticClass;


class DocParser
{
	use TStaticClass;
	
	
	public static function parse(IRawResponse $response): Doc
	{
		$body = $response->getJsonBody();
		$body = is_array($body) ? $body : [];
		
		$doc = new Doc();
		$doc->setSource($body);
		
		return $doc;
	}
}