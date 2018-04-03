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
	
	public function test_drop()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_drop');
		$conn->db()->drop('test_cmddb_drop');
		
		self::assertFalse($conn->db()->exists('test_cmddb_not_exists'));
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
			$info = $conn->db()->compact('test_cmddb_compcat');
		}
		finally
		{
			$conn->db()->drop('test_cmddb_compcat');
		}
	}
}