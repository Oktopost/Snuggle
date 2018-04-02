<?php
namespace sanity;


use PHPUnit\Framework\TestCase;


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
}