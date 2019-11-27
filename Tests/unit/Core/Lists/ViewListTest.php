<?php
namespace Snuggle\Core\Lists;


use PHPUnit\Framework\TestCase;


/**
 * @group unit
 */
class ViewListTest extends TestCase
{
	public function test_count()
	{
		$list = new ViewList();
		
		self::assertEquals(0, $list->count());
		
		$list->Rows[] = new ViewRow();
		$list->Rows[] = new ViewRow();
		
		self::assertEquals(2, $list->count());
	}
}