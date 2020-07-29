<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Commands\CmdBulkStore;
use Snuggle\Core\Doc;
use function foo\func;


class CmdBulkStoreTest extends TestCase
{
	private const DB = 'test_snuggle_cmdbulkstore';
	
	
	private function bulkStoreCmd(): CmdBulkStore
	{
		/** @var CmdBulkStore $res */
		$res = getSanityConnector()->storeAll()->into(self::DB);
		return $res;
	}
	
	private function assertDocumentExists(string $id): void
	{
		self::assertNotNull(self::getDocument($id));
	}
	
	private function getDocument(string $id): ?Doc
	{
		return getSanityConnector()->get()->from(self::DB)->queryDoc($id);
	}
	
	private function getRevision(string $id): ?string
	{
		return getSanityConnector()->get()->from(self::DB)->doc($id)->queryRevision();
	}
	
	private function insertData(array $data): void
	{
		getSanityConnector()->storeAll()
			->into(self::DB)
			->overrideConflict()
			->data($data)
			->execute();
	}
	
	private function assertDataState(array $expected, array $initial, array $change, callable $action)
	{
		$this->bulkStoreCmd()->dataSet($initial)->execute();
		$action($change);
		
		foreach ($expected as $e)
		{
			$doc = $this->getDocument($e['_id']);
			
			$newData = $doc->Data;
			$expectedData = $e;
			unset($expectedData['_id']);
			$newData = jsonencode($newData);
			$expectedData = jsonencode($expectedData);
			
			self::assertTrue($doc->isDataEqualsTo($e), "Expecting $expectedData, got $newData");
		}
	}
	
	private function assertDataStateIsModified(array $initial, array $change, callable $action)
	{
		$this->assertDocModified(array_fill(0, count($initial), true), $initial, $change, $action);
	}
	
	private function assertDocModified(array $expected, array $initial, array $change, callable $action)
	{
		$rev = [];
		$this->bulkStoreCmd()->dataSet($initial)->execute();
		
		foreach ($initial as $item)
		{
			$rev[$item['_id']] = $this->getRevision($item['_id']);
		}
		
		$action($change);
		
		for ($i = 0; $i < count($expected); $i++)
		{
			$id = $initial[$i]['_id'];
			$docRev = $this->getRevision($initial[$i]['_id']);
			
			if (!$expected[$i])
			{
				self::assertEquals($rev[$id], $docRev);
			}
			else
			{
				self::assertNotEquals($rev[$id], $docRev);
			}
		}
	}
	
	
	public static function setUpBeforeClass(): void
	{
		$conn = getSanityConnector();
		
		if (!$conn->db()->exists(self::DB))
			$conn->db()->create(self::DB);
	}
	
	public static function tearDownAfterClass(): void
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists(self::DB))
			$conn->db()->drop(self::DB);
	}
	
	
	public function test_dataStored()
	{
		$this->bulkStoreCmd()->data(['_id' => __FUNCTION__])->execute();
		$this->bulkStoreCmd()->dataSet(
			[
				['_id' => __FUNCTION__ . '1'],
				['_id' => __FUNCTION__ . '2'],
			]
		)->execute();
		
		
		self::assertDocumentExists(__FUNCTION__);
		self::assertDocumentExists(__FUNCTION__ . '1');
		self::assertDocumentExists(__FUNCTION__ . '2');
	}
	
	
	public function test_overrideConflict()
	{
		$this->assertDataState(
			[ // Expected
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 0],
				['_id' => __FUNCTION__ . '2']
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->overrideConflict()->execute();
			}
		);
	}
	
	public function test_ignoreConflict()
	{
		$this->assertDataState(
			[ // Expected
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'c' => 30]
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 10],
				['_id' => __FUNCTION__ . '2', 'b' => 20],
				['_id' => __FUNCTION__ . '3', 'c' => 30]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->ignoreConflict()->execute();
			}
		);
	}
	
	public function test_mergeOverOnConflict()
	{
		$this->assertDataState(
			[ // Expected
				['_id' => __FUNCTION__ . '1', 'a' => 10, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 30]
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 3]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 10, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'c' => 30]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->mergeOverOnConflict()->execute();
			}
		);
	}
	
	public function test_mergeNewOnConflict()
	{
		$this->assertDataState(
			[ // Expected
				['_id' => __FUNCTION__ . '1', 'a' => 1, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 3]
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 3]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 10, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'c' => 30]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->mergeNewOnConflict()->execute();
			}
		);
	}
	
	public function test_resolveConflict()
	{
		$this->assertDataState(
			[ // Expected
				['_id' => __FUNCTION__ . '1', 'a' => 11, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 33]
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2, 'c' => 3]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 10, 'd' => 40],
				['_id' => __FUNCTION__ . '2', 'c' => 30]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->resolveConflict(
					function (Doc $a, Doc $b)
					{
						foreach ($b->Data as $key => $value)
						{
							$a->Data[$key] = ($a->Data[$key] ?? 0) + $value; 
						}
						
						return $a;
					}
				)->execute();
			}
		);
	}
	
	public function test_overrideConflict_forceUpdateUnmodified_NotSet()
	{
		$this->assertDocModified(
			[ // Expected
				false,
				true,
				true
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'c' => 4],
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->overrideConflict()->execute();
			}
		);
	}
	
	public function test_overrideConflict_forceUpdateUnmodified_Set()
	{
		$this->assertDataStateIsModified(
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'c' => 4]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)
					->forceUpdateUnmodified(true)
					->overrideConflict()->execute();
			}
		);
	}
	
	public function test_ignoreConflict_forceUpdateUnmodified()
	{
		$this->assertDocModified(
			[ // Expected
				false
			],
			[ // Initial
				['_id' => __FUNCTION__ . '2']
			],
			[ // Change
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->ignoreConflict()->forceUpdateUnmodified()->execute();
			}
		);
	}
	
	public function test_mergeOverOnConflict_forceUpdateUnmodified_NotSet()
	{
		$this->assertDocModified(
			[ // Expected
				false,
				true,
				false
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3, 'e' => 4]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'e' => 4]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->mergeOverOnConflict()->execute();
			}
		);
	}
	
	public function test_mergeOverOnConflict_forceUpdateUnmodified_Set()
	{
		$this->assertDataStateIsModified(
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3, 'e' => 4]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'e' => 4]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)
					->forceUpdateUnmodified(true)
					->mergeOverOnConflict()->execute();
			}
		);
	}
	
	public function test_mergeNewOnConflict_forceUpdateUnmodified_NotSet()
	{
		$this->assertDocModified(
			[ // Expected
				false,
				true,
				false,
				true
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3],
				['_id' => __FUNCTION__ . '4', 'd' => 4]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'c' => 4],
				['_id' => __FUNCTION__ . '4', 'e' => 5]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->mergeNewOnConflict()->execute();
			}
		);
	}
	
	public function test_mergeNewOnConflict_forceUpdateUnmodified_Set()
	{
		$this->assertDataStateIsModified(
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2'],
				['_id' => __FUNCTION__ . '3', 'c' => 3],
				['_id' => __FUNCTION__ . '4', 'd' => 4]
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2],
				['_id' => __FUNCTION__ . '3', 'c' => 4],
				['_id' => __FUNCTION__ . '4', 'e' => 5]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)
					->forceUpdateUnmodified(true)
					->mergeNewOnConflict()->execute();
			}
		);
	}
	
	
	public function test_resolveConflict_forceUpdateUnmodified_NotSet()
	{
		$this->assertDocModified(
			[ // Expected
				false,
				true
			],
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2']
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)->resolveConflict(function(Doc $a, Doc $b): Doc
					{
						$a->Data['a'] = 1;
						return $a;
					})
					->execute();
			}
		);
	}
	
	public function test_resolveConflict_forceUpdateUnmodified_Set()
	{
		$this->assertDataStateIsModified(
			[ // Initial
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2']
			],
			[ // Change
				['_id' => __FUNCTION__ . '1', 'a' => 1],
				['_id' => __FUNCTION__ . '2', 'b' => 2]
			],
			function (array $data): void
			{
				$this->bulkStoreCmd()->dataSet($data)
					->forceUpdateUnmodified(true)
					->resolveConflict(function(Doc $a, Doc $b): Doc
					{
						$a->Data['a'] = 1;
						return $a;
					})
					->execute();
			}
		);
	}
}