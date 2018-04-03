<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Structura\Arrays;

use Snuggle\Core\Doc;
use Snuggle\Commands\CmdBulkGet;
use Structura\Map;


/**
 * @group integration
 */
class CmdBulkGetTest extends TestCase
{
	private const TABLE_NAME = 'test_snuggle_cmdbulkget';
	
	
	private function getCmd(array $keys = null): CmdBulkGet
	{
		$cmd = getSanityConnector()->getAll()->from(self::TABLE_NAME);
		
		if ($keys)
			$cmd->keys($keys);
		
		return $cmd;
	}
	
	/**
	 * @param array|string $keys
	 * @return null|\Snuggle\Core\Doc
	 */
	private function getFirstKey($keys)
	{
		return $this->getCmd(Arrays::toArray($keys))->queryFirstDoc();
	}
	
	private function insertData(array $data)
	{
		getSanityConnector()->storeAll()
			->into(self::TABLE_NAME)
			->overrideConflict()
			->dataSet($data)
			->execute();
	}
	
	
	public static function setUpBeforeClass()
	{
		$conn = getSanityConnector();
		
		if (!$conn->db()->exists(self::TABLE_NAME))
			$conn->db()->create(self::TABLE_NAME);
	}
	
	public static function tearDownAfterClass()
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists(self::TABLE_NAME))
			$conn->db()->drop(self::TABLE_NAME);
	}
	
	
	public function test_queryFirstDoc_NoDocumentFound_ReturnNull()
	{
		self::assertNull($this->getFirstKey(['notfound_a', 'notfound_b']));
	}
	
	public function test_queryFirstDoc_AtLeastOneDocExists_DocReturned()
	{
		$this->insertData([
			['_id' => 'found_a'],
			['_id' => 'found_b']
		]);
		
		
		$res = $this->getFirstKey(['found_a', 'found_b']);
		
		self::assertInstanceOf(Doc::class, $res);
		self::assertContains($res->ID, ['found_a', 'found_b']);
	}
	
	
	public function test_queryDocsMap_NoDocumentFound_ReturnEmptyMap()
	{
		self::assertEmpty($this->getCmd(['notfound_a', 'notfound_b'])->queryDocsMap());
	}
	
	public function test_queryDocsMap_DocumentsFound_MapReturnedByDocID(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a'],
			['_id' => 'docsMap_b', 'b' => 'c']
		]);
		
		$res = $this->getCmd(['docsMap_a', 'docsMap_b'])->queryDocsMap();
		
		self::assertCount(2, $res);
		self::assertTrue($res->hasAll(['docsMap_a', 'docsMap_b']));
		
		self::assertEquals('c', $res['docsMap_b']->Data['b']);
	}
	
	
	public function test_queryDocsMapBy_NoDocumentFound_ReturnEmptyMap()
	{
		self::assertEmpty($this->getCmd(['notfound_a', 'notfound_b'])->queryDocsMapBy('a'));
	}
	
	public function test_queryDocsMapBy_DocumentsFound_MapReturnedByDocID(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a', 'a' => 'a'],
			['_id' => 'docsMap_b', 'a' => 'c']
		]);
		
		$res = $this->getCmd(['docsMap_a', 'docsMap_b'])->queryDocsMapBy('a');
		
		self::assertCount(2, $res);
		self::assertTrue($res->hasAll(['a', 'c']));
		
		self::assertEquals('c', $res['c']->Data['a']);
	}
	
	public function test_queryDocsMapBy_TargetFieldDoesNotExist_DocMappedToEmptyString(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a']
		]);
		
		$res = $this->getCmd(['docsMap_a'])->queryDocsMapBy('a');
		
		self::assertCount(1, $res);
		self::assertTrue($res->hasAll(['']));
	}
	
	
	public function test_queryDocsGroupBy_NoDocumentFound_ReturnEmptyMap()
	{
		self::assertEmpty($this->getCmd(['notfound_a', 'notfound_b'])->queryDocsGroupBy('a'));
	}
	
	public function test_queryDocsGroupBy_DocumentsFound_MapReturnedByFieldValue(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a', 'a' => 'a'],
			['_id' => 'docsMap_b', 'a' => 'c']
		]);
		
		$res = $this->getCmd(['docsMap_a', 'docsMap_b'])->queryDocsGroupBy('a');
		
		self::assertCount(2, $res);
		self::assertTrue($res->hasAll(['a', 'c']));
		
		self::assertEquals('c', $res['c'][0]->Data['a']);
	}
	
	public function test_queryDocsGroupBy_TargetFieldDoesNotExist_DocGroupedByEmptyString(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a']
		]);
		
		$res = $this->getCmd(['docsMap_a'])->queryDocsGroupBy('a');
		
		self::assertCount(1, $res);
		self::assertTrue($res->hasAll(['']));
	}
	
	public function test_queryDocsGroupBy_SameValueExistsAFewTimes_GroupWithAllDocsExists(): void
	{
		$this->insertData([
			['_id' => 'docsMap_a', 'a' => 'b', 'c' => 1],
			['_id' => 'docsMap_b', 'a' => 'b', 'c' => 2]
		]);
		
		/** @var Doc[][]|Map $res */
		$res = $this->getCmd(['docsMap_a', 'docsMap_b'])->queryDocsGroupBy('a');
		
		self::assertCount(1, $res);
		self::assertTrue($res->has('b'));
		self::assertCount(2, $res['b']);
		
		// Check that docs are different
		$values = [$res['b'][0]->Data['c'], $res['b'][1]->Data['c']];
		
		self::assertContains(1, $values);
		self::assertContains(2, $values);
		
	}
}