<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Core\DB\DBInfo;


/**
 * @group integration
 */
class CmdDBTest extends TestCase
{
	public function test_exists()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_exists');
		
		try
		{
			self::assertTrue($conn->db()->exists('test_cmddb_exists'));
			self::assertFalse($conn->db()->exists('test_cmddb_not_exists'));
		}
		finally
		{
			$conn->db()->drop('test_cmddb_exists');
		}
	}
	
	
	public function test_create()
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists('test_cmddb_create'))
			$conn->db()->drop('test_cmddb_create');
		
		$conn->db()->create('test_cmddb_create', 5);
		
		try
		{
			self::assertTrue($conn->db()->exists('test_cmddb_create'));
			$info =  $conn->db()->info('test_cmddb_create');
		}
		finally
		{
			$conn->db()->drop('test_cmddb_create');
		}
	}
	
	public function test_createIfNotEixsts()
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists('test_snuggle_cmddb_createifnotexists'))
			$conn->db()->drop('test_snuggle_cmddb_createifnotexists');
		
		try
		{
			self::assertTrue($conn->db()->createIfNotExists('test_snuggle_cmddb_createifnotexists', 2));
			self::assertTrue($conn->db()->exists('test_snuggle_cmddb_createifnotexists'));
			
			self::assertFalse($conn->db()->createIfNotExists('test_snuggle_cmddb_createifnotexists'));
		}
		finally
		{
			// Clean up
			$conn->db()->drop('test_snuggle_cmddb_createifnotexists');
		}
	}
	
	public function test_drop()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_drop');
		$conn->db()->drop('test_cmddb_drop');
		
		self::assertFalse($conn->db()->exists('test_cmddb_not_exists'));
	}
	
	public function test_dropIfExists()
	{
		$conn = getSanityConnector();
		
		self::assertFalse($conn->db()->dropIfExists('test_snuggle_database_doesnot_exists'));
		
		$conn->db()->create('test_snuggle_db_to_drop');
		self::assertTrue($conn->db()->dropIfExists('test_snuggle_db_to_drop'));
	}
	
	public function test_info()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_info');
		
		try
		{
			$info = $conn->db()->info('test_cmddb_info');
			
			self::assertInstanceOf(DBInfo::class, $info);
			self::assertEquals('test_cmddb_info', $info->Name); 
		}
		finally
		{
			$conn->db()->drop('test_cmddb_info');
		}
	}
	
	public function test_compact_snity_test()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_compcat');
		
		try
		{
			$conn->db()->compact('test_cmddb_compcat');
		}
		finally
		{
			$conn->db()->drop('test_cmddb_compcat');
		}
	}
}