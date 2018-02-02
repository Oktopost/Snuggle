<?php
namespace Snuggle\Connection\Parsers;


use Snuggle\Core\Doc;
use Snuggle\Base\Connection\Response\IRawResponse;

use Traitor\TStaticClass;


class SingleDocParser
{
	use TStaticClass;
	
	
	private static function getData(array $body): array
	{
		foreach ($body as $key => $value)
		{
			if ($key && $key[0] == '_') 
				unset($body[$key]);
		}
		
		return $body;
	}
	
	
	public static function parse(IRawResponse $response): Doc
	{
		$body = $response->getJsonBody();
		$body = is_array($body) ? $body : [];
		
		$doc = new Doc();
		$doc->setSource($body);
		
		$doc->ID 	= $body['_id'] ?? '';
		$doc->Rev	= $body['_rev'];
		$doc->Data	= self::getData($body);
		
		if (isset($body['_deleted']))
			$doc->IsDeleted = (bool)($body['_deleted']);
				
		$meta = $doc->Meta;
		
		if (isset($body['_local_seq']))
			$meta->LocalSeq = $body['_local_seq'];
		
		return $doc;
	}
}