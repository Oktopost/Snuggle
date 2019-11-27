<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Core\Server\Index;
use Snuggle\Core\StaleBehavior;


/**
 * @group integration
 */
class CmdServerTest extends TestCase
{
	private function createIndexingDB(): string
	{
		$conn = getSanityConnector();
		
		$conn->db()->dropIfExists('test_cmdserver_tasks');
		createTestDB('test_cmdserver_tasks');
		
		try
		{
			$conn->insert()
				->into('test_cmdserver_tasks')
				->data(['hello' => 'world'])
				->execute();
			
			$conn->design()
				->db('test_cmdserver_tasks')
				->name('des')
				->viewsFromDir(__DIR__ . '/design/active_tasks/main')
				->execute();
			
			$conn
				->getAll()
				->from('test_cmdserver_tasks', 'des', 'e')
				->stale(StaleBehavior::UPDATE_AFTER)
				->queryRows();
		}
		catch (\Throwable $T)
		{
			$conn->db()->dropIfExists('test_cmdserver_tasks');
		}
		
		usleep(10000);
		
		return 'test_cmdserver_tasks';
	}
	
	
	public function test_uuids()
	{
		$conn = getSanityConnector();
		$uuids = $conn->server()->UUIDs(5);
		
		self::assertCount(5, $uuids);
		
		foreach ($uuids as $id)
		{
			self::assertTrue(is_string($id));
		}
	}
	
	public function test_databases()
	{
		$conn = getSanityConnector();
		
		createTestDB('test_cmdserver_databases');
		
		try
		{
			$names = $conn->server()->databases();
			self::assertTrue(is_array($names));
		}
		finally
		{
			$conn->db()->drop('test_cmdserver_databases');
		}
	}
	
	
	public function test_activeTasks_noTasks_EmptyArray()
	{
		$conn = getSanityConnector();
		self::assertEmpty($conn->server()->activeTasks());
	}
	
	public function test_activeTasks_HaveRunningTask_TaskReturned()
	{
		$name = null;
		
		try
		{
			$conn = getSanityConnector();
			$name = $this->createIndexingDB();
			
			$data = $conn->server()->activeTasks();
			
			self::assertNotEmpty($data);
		}
		finally
		{
			if ($name)
			{
				$conn->db()->dropIfExists($name);
			}
		}
	}
	
	public function test_activeTasks_HaveRunningTaskButNotInFilter_ReturnEmptyArray()
	{
		$name = null;
		
		try
		{
			$conn = getSanityConnector();
			$name = $this->createIndexingDB();
			
			$data = $conn->server()->activeTasks('abc');
			
			self::assertEmpty($data);
		}
		finally
		{
			if ($name)
			{
				$conn->db()->dropIfExists($name);
			}
		}
	}
	
	
	public function test_info()
	{
		$conn = getSanityConnector();
		
		$info = $conn->server()->info();
		self::assertInstanceOf(Index::class, $info);
	}
}