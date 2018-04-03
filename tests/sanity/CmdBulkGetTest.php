<?php
namespace sanity;


use PHPUnit\Framework\TestCase;

use Structura\Map;
use Structura\Arrays;

use Snuggle\Core\Doc;
use Snuggle\Commands\CmdBulkGet;
use Snuggle\Exceptions\Http\ConflictException;


/**
 * @group integration
 */
class CmdBulkGetTest extends TestCase
{
	private const MAIN_DB		= 'test_snuggle_cmdbulkget';
	private const VIEW_DB		= 'test_snuggle_cmdbulkget_views';
	private const DESIGN_NAME	= 'tests_design';
	private const VIEW_NAME		= 'tests_view';
	
	private const INDEX_FUNCTION = <<<EOF
function (doc)
{
	if (doc.skip === true) return;
	
	emit(doc.index_key, doc.index_value)
}
EOF;
	
	
	private function getCmd(array $keys = null, string $from = self::MAIN_DB): CmdBulkGet
	{
		$cmd = getSanityConnector()->getAll()->from($from);
		
		if ($keys)
			$cmd->keys($keys);
		
		return $cmd;
	}
	
	/**
	 * @param array|string $keys
	 * @return Doc|null
	 */
	private function getFirstKey($keys): ?Doc
	{
		return $this->getCmd(Arrays::toArray($keys))->queryFirstDoc();
	}
	
	private function insertData(array $data, string $into = self::MAIN_DB): void
	{
		getSanityConnector()->storeAll()
			->into($into)
			->overrideConflict()
			->dataSet($data)
			->execute();
	}
	
	
	public static function setUpBeforeClass(): void
	{
		$conn = getSanityConnector();
		
		if (!$conn->db()->exists(self::MAIN_DB))
			$conn->db()->create(self::MAIN_DB);
		
		if (!$conn->db()->exists(self::VIEW_DB))
			$conn->db()->create(self::VIEW_DB);
		
		try
		{
			$conn->direct()
				->setPUT(
					self::VIEW_DB . '/_design/' . self::DESIGN_NAME, 
					[],
					jsonencode([
						'views' => [
							self::VIEW_NAME => [
								'map' => self::INDEX_FUNCTION
							]
						]
					])
				)
				->execute();
		}
		catch (ConflictException $conflictException) {}
	}
	
	public static function tearDownAfterClass(): void
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists(self::MAIN_DB))
			$conn->db()->drop(self::MAIN_DB);
		
		if ($conn->db()->exists(self::VIEW_DB))
			$conn->db()->drop(self::VIEW_DB);
	}
	
	
	public function test_queryFirstDoc_NoDocumentFound_ReturnNull(): void
	{
		self::assertNull($this->getFirstKey(['notfound_a', 'notfound_b']));
	}
	
	public function test_queryFirstDoc_AtLeastOneDocExists_DocReturned(): void
	{
		$this->insertData([
			['_id' => 'found_a'],
			['_id' => 'found_b']
		]);
		
		
		$res = $this->getFirstKey(['found_a', 'found_b']);
		
		self::assertInstanceOf(Doc::class, $res);
		self::assertContains($res->ID, ['found_a', 'found_b']);
	}
	
	
	public function test_queryDocsMap_NoDocumentFound_ReturnEmptyMap(): void
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
	
	
	public function test_queryDocsMapBy_NoDocumentFound_ReturnEmptyMap(): void
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
	
	
	public function test_queryDocsGroupBy_NoDocumentFound_ReturnEmptyMap(): void
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
	
	
	public function test_ViewNameGiven_QueryExecutedFromAView(): void
	{
		$this->insertData(
			[
				['_id' => 'ind_a', 'index_key' => ['a'], 'index_value' => null]
			],
			self::VIEW_DB);
		
		$res = $this->getCmd([['a']], self::VIEW_DB)
			->view(self::DESIGN_NAME, self::VIEW_NAME)
			->queryList();
		
		self::assertCount(1, $res->Rows);
	}
	
	public function test_ViewNameNotGiven_QueryExecutedFromAllDocs(): void
	{
		$this->insertData(
			[
				['_id' => 'ind_a', 'index_key' => ['a'], 'index_value' => null]
			],
			self::VIEW_DB);
		
		$res = $this->getCmd(['ind_a'], self::VIEW_DB)
			->queryList();
		
		self::assertCount(1, $res->Rows);
	}
	
	
	public function test_queryExists_NothingFound_ReturnFalse(): void
	{
		self::assertFalse($this->getCmd(['notfound_a', 'notfound_b'])->queryExists());
	}
	
	public function test_queryExists_RecordsExist_ReturnTrue(): void
	{
		$this->insertData([
			['_id' => 'item_a']
		]);
		
		self::assertTrue($this->getCmd(['item_a'])->queryExists());
	}
}