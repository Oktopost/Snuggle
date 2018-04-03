<?php
namespace Snuggle\Connection\Parsers\Lists;


use Snuggle\Core\Lists\ViewRow;
use Snuggle\Core\Lists\ViewList;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Connection\Parsers\SingleDocParser;

use Traitor\TStaticClass;


class ViewListParser
{
	use TStaticClass;
	
	
	/**
	 * @param array $data
	 * @return ViewRow[]
	 */
	public static function getRows(array $data): array
	{
		$res = [];
		
		foreach ($data['rows'] ?? [] as $row)
		{
			if (!isset($row['id']) || isset($row['error']))
				continue;
			
			$viewRow = new ViewRow();
			
			$viewRow->DocID = $row['id'];
			$viewRow->Key	= $row['key'] ?? null;
			$viewRow->Value	= $row['value'] ?? null;
			
			if (isset($row['doc']))
			{
				$viewRow->Doc = SingleDocParser::parseData($row['doc']);
			}
			
			$res[] = $viewRow;
		}
		
		return $res;
	}
	
	public static function parseArray(array $data): ViewList
	{
		$list = new ViewList();
		
		$list->Offset		= (isset($data['offset']) ? $data['offset'] - 1 : 0);
		$list->Total 		= $data['total_rows'] ?? null;
		$list->UpdateSeq	= $data['update_seq'] ?? null;
		$list->Rows			= self::getRows($data);
		
		return $list;
	}
	
	public static function parseResponse(IRawResponse $response): ViewList
	{
		return self::parseArray($response->getJsonBody());
	}
}