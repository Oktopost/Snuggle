<?php
namespace Snuggle\Config;


use PHPUnit\Framework\TestCase;
use Snuggle\Exceptions\ConfigurationAlreadyDefinedException;
use Snuggle\Exceptions\ConfigurationNotFoundException;
use Snuggle\Exceptions\InvalidConfigFormatException;


class ConnectionConfigsManagerTest extends TestCase
{
	public function test_has_HasLoaded_ReturnTrue()
	{
		$subject = new ConnectionConfigsManager();
		$subject->add('main');
		
		self::assertTrue($subject->has('main'));
	}
	
	public function test_has_NoLoaders_ReturnFalse()
	{
		$subject = new ConnectionConfigsManager();
		
		self::assertFalse($subject->has('main'));
	}
	
	public function test_has_CouldNotLoad_ReturnFalse()
	{
		$loadersMock = $this->getMockBuilder(LoadersCollection::class)->getMock();
		$loadersMock->expects($this->once())->method('tryLoad')->willReturn(null);
		
		$reflectionClass = new \ReflectionClass(ConnectionConfigsManager::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		
		$loaders->setValue($subject, $loadersMock);
		
		self::assertFalse($subject->has('main'));
	}
	
	public function test_has_LoadedSuccessfully_ReturnTrue()
	{
		$loadersMock = $this->getMockBuilder(LoadersCollection::class)->getMock();
		$loadersMock->expects($this->once())->method('tryLoad')->willReturn([]);
		
		$reflectionClass = new \ReflectionClass(ConnectionConfigsManager::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		
		$loaders->setValue($subject, $loadersMock);
		
		self::assertTrue($subject->has('main'));
	}
	
	public function test_get_NewMainConnection_CreateDefaultAndReturn()
	{
		$subject = new ConnectionConfigsManager();
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get());
	}
	
	public function test_get_ConfigNotExistsAndCouldNotBeLoaded_ExceptionThrown()
	{
		$this->expectException(ConfigurationNotFoundException::class);
		
		$loadersMock = $this->getMockBuilder(LoadersCollection::class)->getMock();
		$loadersMock->expects($this->once())->method('tryLoad')->willReturn(null);
		
		$reflectionClass = new \ReflectionClass(ConnectionConfigsManager::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		
		$loaders->setValue($subject, $loadersMock);
		
		$subject->get();
	}
	
	public function test_get_ConfigLoaded_ReturnConfig()
	{
		$loadersMock = $this->getMockBuilder(LoadersCollection::class)->getMock();
		$loadersMock->expects($this->once())->method('tryLoad')->willReturn([]);
		
		$reflectionClass = new \ReflectionClass(ConnectionConfigsManager::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		
		$loaders->setValue($subject, $loadersMock);
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get());
	}
	
	public function test_addLoaders_LoadersCollectionNull_CreateNewAndAddGivenLoaders()
	{
		$reflectionClass = new \ReflectionClass(ConnectionConfigsManager::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		
		$subject->addLoaders([]);
		
		self::assertInstanceOf(LoadersCollection::class, $loaders->getValue($subject));
	}
	
	public function test_add_ConfigAlreadyExists_ExceptionThrown()
	{
		$this->expectException(ConfigurationAlreadyDefinedException::class);
		
		$subject = new ConnectionConfigsManager();
		$subject->add('main');
		
		$subject->add('main');
	}
	
	public function test_add_DataArray_CreateAndAddToConfigs()
	{
		$subject = new ConnectionConfigsManager();
		$subject->add('test', []);
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get('test'));
	}
	
	public function test_add_DataConfig_AddToConfigs()
	{
		$config = new ConnectionConfig();
		
		$subject = new ConnectionConfigsManager();
		$subject->add('test', $config);
		
		self::assertEquals($config, $subject->get('test'));
	}
	
	public function test_add_DataInterfaceInSkeleton_LoadImplementerAndAddAsConfig()
	{
		
	}
	
	public function test_add_DataClassInSkeleton_LoadAndAddAsConfig()
	{
		
	}
	
	public function test_add_DataNotValidConfig_ExceptionThrown()
	{
		$this->expectException(InvalidConfigFormatException::class);
		
		$subject = new ConnectionConfigsManager();
		$subject->add('test', 'SomeString');
	}
	
	public function test_add_DataNull_UseEmptyArrayAsConfig()
	{
		$subject = new ConnectionConfigsManager();
		$subject->add('test');
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get('test'));
	}
	
	public function test_add_ItemNull_UseMainAsDefault()
	{
		$subject = new ConnectionConfigsManager();
		$subject->add(null);
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get('main'));
	}
	
	public function test_add_ItemArray_UseItemAsDataAndMainAsDefault()
	{
		$subject = new ConnectionConfigsManager();
		$subject->add([]);
		
		self::assertInstanceOf(ConnectionConfig::class, $subject->get('main'));
	}
}