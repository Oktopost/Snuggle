<?php
namespace Snuggle\Connection\Parsers\Lists;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Core\Lists\ChangeRow;
use Snuggle\Core\Lists\ChangeList;
use Snuggle\Core\Document\RevisionInfo;
use Snuggle\Connection\Parsers\SingleDocParser;

use Traitor\TStaticClass;


class ChangesListParser
{
	use TStaticClass;


	private static function getChanges(array $changes): array
	{
		$result = [];

		foreach ($changes as $change)
		{
			$result[] = new RevisionInfo($change['rev']);
		}

		return $result;
	}


	/**
	 * @param array $data
	 * @return ChangeRow[]
	 */
	public static function getRows(array $data): array
	{
		$res = [];

		foreach ($data['results'] ?? [] as $row)
		{
			if (isset($row['error']))
				continue;

			$changeRow = new ChangeRow();

			$changeRow->DocID   = $row['id'] ?? null;
			$changeRow->Seq     = $row['seq'] ?? null;
			$changeRow->Changes = self::getChanges($row['changes'] ?? []);

			if (isset($row['doc']))
			{
				$changeRow->Doc = SingleDocParser::parseData($row['doc']);
			}

			$res[] = $changeRow;
		}

		return $res;
	}

	public static function parseArray(array $data): ChangeList
	{
		$list = new ChangeList();

		$list->LastSeq = $data['last_seq'] ?? null;
		$list->Pending = $data['pending'] ?? 0;
		$list->Rows    = self::getRows($data);

		return $list;
	}

	public static function parseResponse(IRawResponse $response): ChangeList
	{
		return self::parseArray($response->getJsonBody());
	}
}
