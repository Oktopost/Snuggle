<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Core\Server\Index;


class CmdServerTest extends TestCase
{
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
		
		$conn->db()->create('test_cmdserver_databases');
		
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
	
	public function test_info()
	{
		$conn = getSanityConnector();
		
		$info = $conn->server()->info();
		self::assertInstanceOf(Index::class, $info);
	}
}