<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Commands\CmdGet;


/**
 * @group integration
 */
class CmdGetTest extends TestCase
{
	private const DB = 'test_snuggle_cmdget';
	
	
	private function getCmd(): CmdGet
	{
		/** @var CmdGet $res */
		$res = getSanityConnector()->get()->from(self::DB);
		
		return $res;
	}
	
	private function insertData(array $data): void
	{
		getSanityConnector()->storeAll()
			->into(self::DB)
			->overrideConflict()
			->data($data)
			->execute();
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
	
	
	public function test_ignoreMissing_DocumentNotFound_ReturnNull()
	{
		self::assertNull($this->getCmd()->ignoreMissing()->queryDoc('notfound'));
	}
	
	public function test_queryDoc_DocumentExists_DocumentReturned()
	{
		$this->insertData(['_id' => 'a', 'b' => 2]);
		
		$doc = $this->getCmd()->queryDoc('a');
		
		self::assertNotNull($doc);
		self::assertEquals(['b' => 2], $doc->Data);
	}
	
	public function test_queryExists()
	{
		$this->insertData(['_id' => 'a', 'b' => 2]);
		
		self::assertFalse($this->getCmd()->queryExists('notfond'));
		self::assertTrue($this->getCmd()->queryExists('a'));
	}
}