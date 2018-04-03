<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Core\Doc;
use Structura\Arrays;


class CmdBulkGetTest extends TestCase
{
	private const TABLE_NAME = 'test_snuggle_cmdbulkget';
	
	
	private function getCmd()
	{
		return getSanityConnector()->getAll()->from(self::TABLE_NAME);
	}
	
	/**
	 * @param array|string $keys
	 * @return null|\Snuggle\Core\Doc
	 */
	private function getFirstKey($keys)
	{
		return $this->getCmd()->keys(Arrays::toArray($keys))->queryFirst();
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
	
	
	public function test_queryFirst_NoDocumentFound_ReturnNull()
	{
		self::assertNull($this->getFirstKey(['notfound_a', 'notfound_b']));
	}
	
	public function test_queryFirst_AtLeastOneDocExists_DocReturned()
	{
		
		getSanityConnector()->storeAll()
			->into(self::TABLE_NAME)
			->overrideConflict()
			->dataSet([
				[
					'_id' => 'found_a'
				],
				[
					'_id' => 'found_b'
				]
			])
			->execute();
		
		$res = $this->getFirstKey(['found_a', 'found_b']);
		
		self::assertInstanceOf(Doc::class, $res);
		self::assertContains($res->ID, ['found_a', 'found_b']);
	}
}