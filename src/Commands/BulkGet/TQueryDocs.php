<?php
namespace Snuggle\Commands\BulkGet;


use Snuggle\Core\Doc;
use Snuggle\Connection\Parsers\Lists\AllDocsListParser;

use Structura\Map;


/**
 * @mixin \Snuggle\Commands\CmdBulkGet
 */
trait TQueryDocs
{
	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array
	{
		return AllDocsListParser::getDocuments(
			(clone $this)
				->includeDocs()
				->queryJson());
	}
	
	/**
	 * @return string[]|Map
	 */
	public function queryRevisions(): Map
	{
		$map = new Map();
		
		$command = clone $this;
		$command
			->from($this->db)
			->updateSeq(false)
			->includeDocs(false);
		
		$result = $command->queryJson();
		
		foreach ($result['rows'] as $row)
		{
			if (!isset($row['id']) || !isset($row['value']['rev']))
				continue;
			
			$map[$row['id']] = $row['value']['rev'];
		}
		
		return $map;
	}
	
	/**
	 * @return Doc[]|Map
	 */
	public function queryDocsMap(): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		foreach ($docs as $doc)
		{
			$map->add($doc->ID, $doc);
		}
		
		return $map;
	}
	
	/**
	 * @return Doc|null
	 */
	public function queryFirstDoc(): ?Doc
	{
		$command = clone $this;
		
		$docs = $command
			->limit(1)
			->queryDocs();
		
		return $docs ? $docs[0] : null;
	}
	
	/**
	 * @param string $field
	 * @return Doc[]|Map
	 */
	public function queryDocsMapBy(string $field): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		$fields = explode('.', $field);
		
		foreach ($docs as $doc)
		{
			$map->add($doc->getKey($fields, ''), $doc);
		}
		
		return $map;
	}
	
	/**
	 * @param string $field
	 * @return Doc[][]|Map
	 */
	public function queryDocsGroupBy(string $field): Map
	{
		$docs = $this->queryDocs();
		$map = new Map();
		
		$fields = explode('.', $field);
		
		foreach ($docs as $doc)
		{
			$key = $doc->getKey($fields, '');
			
			if (!$map->has($key))
			{
				$map->add($key, [$doc]);
			}
			else
			{
				$map->add($key, array_merge($map->get($key), [$doc]));
			}
		}
		
		return $map;
	}
}