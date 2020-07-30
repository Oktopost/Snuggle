<?php
namespace sanity;


use Objection\Mapper;
use PHPUnit\Framework\TestCase;
use Snuggle\Core\DB\DBInfo;
use Snuggle\Exceptions\Http\NotFoundException;


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
	
	public function test_compact_sanity_test()
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
	
	public function test_compact_sanityForDesign_test()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_compcat_2');
		
		try
		{
			$conn->design()
				->db('test_cmddb_compcat_2')
				->name('des')
				->viewsFromDir(__DIR__ . '/design/db')
				->execute();
				
			$conn->db()->compact('test_cmddb_compcat_2', 'des');
		}
		finally
		{
			$conn->db()->drop('test_cmddb_compcat_2');
		}
	}
	
	public function test_setRevisionsLimit_sanity_test()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_rev_limit');
		
		try
		{
			$conn->db()->setRevisionsLimit('test_cmddb_rev_limit', 10);
		}
		finally
		{
			$conn->db()->drop('test_cmddb_rev_limit');
		}
	}
	
	
	public function test_designDocs_NoDesignDocs_ReturnEmptyArray()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_designdocs_empty');
		
		try
		{
			self::assertEmpty($conn->db()->designDocs('test_cmddb_designdocs_empty'));
		}
		catch (NotFoundException $t)
		{
			// For CouchDB < 2.0 a not found is thrown 
		}
		finally
		{
			$conn->db()->drop('test_cmddb_designdocs_empty');
		}
	}
	
	
	public function test_designDocs_HaveDesignDocs_NamesReturned()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_designdocs_have');
		
		try
		{
			$conn->design()
				->db('test_cmddb_designdocs_have')
				->name('a')
				->viewsFromDir(__DIR__ . '/design/db/designdoc/a')
				->execute();
			
			$conn->design()
				->db('test_cmddb_designdocs_have')
				->name('c')
				->viewsFromDir(__DIR__ . '/design/db/designdoc/c')
				->execute();
			
			$ddocs = $conn->db()->designDocs('test_cmddb_designdocs_have');
			sort($ddocs);
			
			self::assertEquals(['a', 'c'], $ddocs);
		}
		catch (NotFoundException $t)
		{
			// For CouchDB < 2.0 a not found is thrown on travis
		}
		finally
		{
			$conn->db()->drop('test_cmddb_designdocs_have');
		}
	}
	
	
	public function test_designDocInfo()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmddb_ddoc_info');
		
		try
		{
			$conn->design()
				->db('test_cmddb_ddoc_info')
				->name('my_design_doc')
				->viewsFromDir(__DIR__ . '/design/db/designdoc/a')
				->execute();
			
			$info = $conn->db()->designDocInfo('test_cmddb_ddoc_info', 'my_design_doc');
			
			self::assertEquals('my_design_doc', $info->Name);
			self::assertEquals('javascript', $info->Language);
			self::assertNotEquals(0, $info->DiskSize);
		}
		finally
		{
			$conn->db()->drop('test_cmddb_ddoc_info');
		}
	}
}