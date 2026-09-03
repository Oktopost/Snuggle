<?php
namespace Snuggle\Connection\Parsers\Lists;


use PHPUnit\Framework\TestCase;


/**
 * @group unit
 */
class ChangesListParserTest extends TestCase
{
	public function test_parseArray_ChangesResponseParsed(): void
	{
		$list = ChangesListParser::parseArray([
			'last_seq' => '12-sequence',
			'pending'  => 3,
			'results'  => [
				[
					'id'      => 'doc-1',
					'seq'     => '11-sequence',
					'changes' => [['rev' => '2-revision']],
					'doc'     => ['_id' => 'doc-1', '_rev' => '2-revision', 'Value' => 42]
				],
				[
					'id'    => 'doc-2',
					'error' => 'forbidden'
				]
			]
		]);

		self::assertSame('12-sequence', $list->LastSeq);
		self::assertSame(3, $list->Pending);
		self::assertSame(1, $list->count());
		self::assertSame('doc-1', $list->Rows[0]->DocID);
		self::assertSame('11-sequence', $list->Rows[0]->Seq);
		self::assertSame('2-revision', $list->Rows[0]->Changes[0]->Rev);
		self::assertTrue($list->Rows[0]->hasDoc());
		self::assertSame(42, $list->Rows[0]->Doc->Data['Value']);
	}
}
