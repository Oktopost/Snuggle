<?php
namespace Snuggle\Core\Lists;


use PHPUnit\Framework\TestCase;
use Snuggle\Core\Doc;


/**
 * @group unit
 */
class ViewRowTest extends TestCase
{
	public function test_hasDoc_NoDoc_ReturnFalse()
	{
		$row = new ViewRow();
		self::assertFalse($row->hasDoc());
	}
	
	public function test_hasDoc_HasDoc_ReturnTrue()
	{
		$row = new ViewRow();
		$row->Doc = new Doc();
		
		self::assertTrue($row->hasDoc());
	}
}