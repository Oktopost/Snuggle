<?php
namespace Snuggle\Connection\Parsers\DB;


use PHPUnit\Framework\TestCase;
use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Core\DB\DDocInfo;


/**
 * @group unit
 */
class DDocInfoParserTest extends TestCase
{
	protected function getResponse(array $data): IRawResponse
	{
		$mock = $this->getMockBuilder(IRawResponse::class)->getMock();
		$mock->method('getJsonBody')->willReturn($data);
		
		/** @var IRawResponse $mock */
		return $mock;
	}
	
	
	public function test_sanity_missing_info(): void
	{
		$obj = DDocInfoParser::parse($this->getResponse([]));
		self::assertInstanceOf(DDocInfo::class, $obj);
	}
	
	public function test_sanity_HaveData_DataParsed(): void
	{
		$obj = DDocInfoParser::parse($this->getResponse(
			[
				"name" => "view",
				"view_index" => [
					"compact_running" => false,
					"data_size" => 926691,
					"disk_size" => 1982704,
					"language" => "python",
					"purge_seq" => 0,
					"signature" => "a59a1bb13fdf8a8a584bc477919c97ac",
					"update_seq" => 12397,
					"updater_running" => false,
					"waiting_clients" => 0,
					"waiting_commit" => false
				]
			]));
		
		self::assertEquals('view', $obj->Name);
		self::assertEquals(1982704, $obj->DiskSize);
		self::assertEquals(926691, $obj->DataSize);
	}
}