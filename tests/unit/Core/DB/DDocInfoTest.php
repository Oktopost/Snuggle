<?php
namespace Snuggle\Core\DB;


use PHPUnit\Framework\TestCase;


/**
 * @group unit
 */
class DDocInfoTest extends TestCase
{
	public function test_dataToDiskSizeRatio_DiskSizeIsZero_ReturnZero(): void
	{
		$subject = new DDocInfo();
		$subject->DataSize = 1235;
		
		self::assertEquals(0.0, $subject->getDataToDiskSizeRatio());
	}
	
	public function test_dataToDiskSizeRatio_DataCalculated(): void
	{
		$subject = new DDocInfo();
		
		$subject->DataSize = 500;
		$subject->DiskSize = 1500;
		
		self::assertEquals(0.33, $subject->getDataToDiskSizeRatio(), '', 0.01);
	}
}