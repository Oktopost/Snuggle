<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Commands\CmdGet;
use Snuggle\Commands\CmdStore;
use Snuggle\Core\Doc;
use Snuggle\Exceptions\Http\ConflictException;


/**
 * @group integration
 */
class CmdStoreTest extends TestCase
{
	private const DB = 'test_snuggle_cmdstore';
	
	
	private function storeCmd(): CmdStore
	{
		/** @var CmdStore $res */
		$res = getSanityConnector()->store()->into(self::DB);
		return $res;
	}
	
	private function getDocument(string $id): ?Doc
	{
		return getSanityConnector()->get()->from(self::DB)->queryDoc($id);
	}
	
	private function getRevision(string $id): ?string
	{
		return getSanityConnector()->get()->from(self::DB)->doc($id)->queryRevision();
	}
	
	
	private function assertObjectRevisionChange(bool $isExpectedToChange, callable $operation)
	{
		$doc = ['_id' => __FUNCTION__, 'a' => 1];
		$this->storeCmd()->data($doc)->execute();
		
		$rev = $this->getRevision(__FUNCTION__);
		
		
		$operation($doc);
		
		
		self::assertNotNull($rev);
		
		if ($isExpectedToChange)
		{
			self::assertNotEquals($rev, $this->getRevision(__FUNCTION__));
		}
		else
		{
			self::assertEquals($rev, $this->getRevision(__FUNCTION__));
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
	
	
	public function test_storeItem_itemStored()
	{
		$this->storeCmd()
			->data([
				'_id' => __FUNCTION__,
				'a' => 2
			])
			->execute();
		
		$doc = $this->getDocument(__FUNCTION__);
		
		self::assertNotNull($doc);
		self::assertEquals(2, $doc->Data['a']);
	}
	
	public function test_storeAlreadyExistingItem_ConflictErrorThrown()
	{
		$this->storeCmd()->data(['_id' => __FUNCTION__])->execute();
		
		$cmd = $this->storeCmd()
			->data(['_id' => __FUNCTION__])
			->failOnConflict();
		
		$this->expectException(ConflictException::class);
		$cmd->execute();
	}
	
	
	public function test_override_existingItemUpdated()
	{
		$this->storeCmd()
			->data(['_id' => __FUNCTION__, 'a' => 1])
			->execute();
		
		
		$this->storeCmd()
			->data(['_id' => __FUNCTION__, 'a' => 2])
			->overrideConflict()
			->execute();
		
		
		$doc = $this->getDocument(__FUNCTION__);
		
		self::assertNotNull($doc);
		self::assertEquals(2, $doc->Data['a']);
	}
	
	public function test_overrideConflict_forceUpdateUnmodified()
	{
		self::assertObjectRevisionChange(
			false,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->overrideConflict()
					->execute();
			});
		
		self::assertObjectRevisionChange(
			true,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->overrideConflict()
					->forceUpdateUnmodified()
					->execute();
			});
	}
	
	public function test_mergeNewOnConflict_forceUpdateUnmodified()
	{
		self::assertObjectRevisionChange(
			false,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->mergeNewOnConflict()
					->execute();
			});
		
		self::assertObjectRevisionChange(
			true,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->mergeNewOnConflict()
					->forceUpdateUnmodified()
					->execute();
			});
	}
	
	public function test_mergeOverOnConflict_forceUpdateUnmodified()
	{
		self::assertObjectRevisionChange(
			false,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->mergeOverOnConflict()
					->execute();
			});
		
		self::assertObjectRevisionChange(
			true,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->mergeOverOnConflict()
					->forceUpdateUnmodified()
					->execute();
			});
	}
	
	public function test_resolveConflict_ReturnOriginalObject_DataNotModified()
	{
		self::assertObjectRevisionChange(
			false,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->resolveConflict(function ($a) { return $a; })
					->execute();
			});
	}
	
	public function test_resolveConflict_ReturnExistingObject_DataModified()
	{
		self::assertObjectRevisionChange(
			true,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->resolveConflict(function ($a, $b) { return $b; })
					->execute();
			});
	}
	
	public function test_resolveConflict_ReturnDataWithoutRevision_RevisionCopied()
	{
		self::assertObjectRevisionChange(
			true,
			function(array $data) use (&$id)
			{
				$this->storeCmd()
					->data($data)
					->resolveConflict(
						function($a, Doc $b) use (&$id)
						{
							$c = new Doc();
							
							$id = $b->ID;
							$c->ID = $b->ID;
							$c->Data = ['a' => 78912];
							
							return $c;
						})
					->execute();
			});
		
		$doc = $this->getDocument($id);
		
		self::assertEquals(['a' => 78912], $doc->Data);
	}
	
	public function test_resolveConflict_forceUpdateUnmodified()
	{
		self::assertObjectRevisionChange(
			false,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->resolveConflict(function ($a) { return $a; })
					->execute();
			});
		
		self::assertObjectRevisionChange(
			true,
			function (array $data)
			{
				$this->storeCmd()
					->data($data)
					->resolveConflict(function ($a) { return $a; })
					->forceUpdateUnmodified()
					->execute();
			});
	}
	
	public function test_onConflictWith_ForceUpdateUnmodifiedFlagIsFalse_ReturnDocumentsResult()
	{
		$doc = ['_id' => __FUNCTION__, 'a' => 1];
		$this->storeCmd()->data($doc)->execute();
		
		self::assertEquals(200, $this->storeCmd()->data($doc)->overrideConflict()->queryCode());
		self::assertEquals(200, $this->storeCmd()->data($doc)->mergeOverOnConflict()->queryCode());
		self::assertEquals(200, $this->storeCmd()->data($doc)->mergeNewOnConflict()->queryCode());
		self::assertEquals(200, $this->storeCmd()->data($doc)->resolveConflict(function ($a) { return $a; })->queryCode());
	}
}