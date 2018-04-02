<?php
namespace Snuggle\Connection\Parsers\Lists;


use Snuggle\Core\Doc;
use Snuggle\Core\Lists\AllDocsList;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Connection\Parsers\SingleDocParser;

use Traitor\TStaticClass;


class AllDocsListParser
{
	use TStaticClass;
	
	
	/**
	 * @param array $data
	 * @return array
	 */
	public static function getDocuments(array $data): array
	{
		$res = [];
		
		foreach ($data['rows'] ?? [] as $row)
		{
			if (!isset($row['id']) || isset($row['error']))
				continue;
			
			$doc = new Doc();
			
			$doc->ID	= $row['id'] ?? null;
			$doc->Rev	= $row['value']['rev'];
			
			if (isset($row['doc']))
			{
				$doc->Data = SingleDocParser::getData($row['doc'] ?? []);
			}
			
			$res[] = $doc;
		}
		
		return $res;
	}
	
	public static function parseArray(array $data): AllDocsList
	{
		$list = new AllDocsList();
		
		$list->Offset		= (isset($data['offset']) ? $data['offset'] - 1 : 0);
		$list->Total 		= $data['total_rows'] ?? null;
		$list->UpdateSeq	= $data['update_seq'] ?? null;
		$list->Docs[]		= self::getDocuments($data);
		
		return $list;
	}
	
	public static function parseResponse(IRawResponse $response): AllDocsList
	{
		return self::parseArray($response->getJsonBody());
	}
}