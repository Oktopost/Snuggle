<?php
namespace Snuggle\Config;


use PHPUnit\Framework\TestCase;
use Snuggle\Exceptions\FatalSnuggleException;


class ConnectionConfigTest extends TestCase
{
	public function test_setCredentials_SetsCredentials()
	{
		$subject = new ConnectionConfig();
		
		$subject->setCredentials('test', '111');
		
		self::assertEquals('test', $subject->User);
		self::assertEquals('111', $subject->Password);
	}
	
	public function test_isHttp_HTTP_ReturnTrue()
	{
		$subject = new ConnectionConfig();
		
		self::assertTrue($subject->isHttp());
	}
	
	public function test_isHttp_NotHTTP_ReturnFalse()
	{
		$subject = new ConnectionConfig();
		$subject->Protocol = 'https';
		
		self::assertFalse($subject->isHttp());
	}
	
	public function test_isHttps_HTTPS_ReturnTrue()
	{
		$subject = new ConnectionConfig();
		$subject->Protocol = 'https';
		
		self::assertTrue($subject->isHttps());
	}
	
	public function test_isHttps_NotHTTPS_ReturnFalse()
	{
		$subject = new ConnectionConfig();
		
		self::assertFalse($subject->isHttps());
	}
	
	public function test_debugInfo_DontReturnPassword()
	{
		$subject = new ConnectionConfig();
		$subject->Password = '1234';
		
		self::assertEquals('********', $subject->__debugInfo()['Password']);
	}
	
	
	public function test_create_GenericKeySet()
	{
		$subject1 = ConnectionConfig::create(['generic' => ['a']]);
		$subject2 = ConnectionConfig::create(['generic' => []]);
		$subject3 = ConnectionConfig::create(['generic' => null]);
		
		self::assertEquals(['a'], $subject1->Generic);
		self::assertEquals([], $subject2->Generic);
		self::assertEquals([], $subject3->Generic);
	}
	
	public function test_create_GenericKeyIsInvalid_ErrorThrown()
	{
		$this->expectException(FatalSnuggleException::class);
		ConnectionConfig::create(['generic' => 123]);
	}
}