<?php
namespace Snuggle\Connection\Parsers\Lists;


use PHPUnit\Framework\TestCase;


/**
 * @group unit
 */
class ViewListParserTest extends TestCase
{
	public function test_parseArray_EmptyArrayPassed_DefaultValuesUsed()
	{
		$res = ViewListParser::parseArray([]);
		
		self::assertEquals(0, $res->Offset);
		self::assertEquals(0, $res->Total);
		self::assertEquals(null, $res->UpdateSeq);
		self::assertEquals([], $res->Rows);
	}
	
	public function test_parseArray_ListPropertiesSet()
	{
		$res = ViewListParser::parseArray([
			'total_rows'	=> 127,
			'offset'		=> 1024,
			'update_seq'	=> 'abc'
		]);
		
		self::assertEquals(1024, $res->Offset);
		self::assertEquals(127, $res->Total);
		self::assertEquals('abc', $res->UpdateSeq);
		self::assertEquals([], $res->Rows);
	}
	
	public function test_parseArray_OffsetFixed()
	{
		$res = ViewListParser::parseArray([
			'offset' => 0
		]);
		
		self::assertEquals(0, $res->Offset);
	}
	
	public function test_parseArray_RowsPreset_RowsParsed()
	{
		$res = ViewListParser::parseArray([
			'rows' => [
				[
					'id' 	=> 123,
					'key'	=> 'abc',
					'value'	=> ['a' => 'b']
				],
				[
					'id' 	=> 123,
					'key'	=> 'abc',
					'value'	=> ['a' => 'b']
				]
			]
		]);
		
		self::assertCount(2, $res->Rows);
		
		self::assertEquals(123, $res->Rows[0]->DocID);
		self::assertEquals('abc', $res->Rows[0]->Key);
		self::assertEquals(['a' => 'b'], $res->Rows[0]->Value);
		self::assertNull($res->Rows[0]->Doc);
	}
	
	public function test_parseArray_DocumentPresent_DocParsed()
	{
		$res = ViewListParser::parseArray([
			'rows' => [
				[
					'id' 	=> 123,
					'key'	=> 'abc',
					'value'	=> ['a' => 'b'],
					'doc'	=> [
						'_id'	=> 123,
						'a'		=> 124
					]
				]
			]
		]);
		
		self::assertNotNull($res->Rows[0]->Doc);
		self::assertEquals(124, $res->Rows[0]->Doc->Data['a']);
	}
}