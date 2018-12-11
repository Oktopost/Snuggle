<?php
namespace Snuggle\Config;


use PHPUnit\Framework\TestCase;


class LoadersCollectionTest extends TestCase
{
	public function test_addLoader_LoaderArray_LoadAllItems()
	{
		$mockLoader = $this->getMockBuilder(IConfigLoader::class)->getMock();
		
		$reflectionClass = new \ReflectionClass(LoadersCollection::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		$subject->addLoader([$mockLoader]);
		
		self::assertEquals([$mockLoader], $loaders->getValue($subject));
	}
	
	public function test_addLoader_LoaderIsClassString_LoadFromSkeleton()
	{
		
	}
	
	public function test_addLoader_LoaderIsInterfaceString_LoadFromSkeleton()
	{
		
	}
	
	public function test_addLoader_LoaderIsIConfigLoader_AddLoader()
	{
		$mockLoader = $this->getMockBuilder(IConfigLoader::class)->getMock();
		
		$reflectionClass = new \ReflectionClass(LoadersCollection::class);
		$loaders = $reflectionClass->getProperty('loaders');
		$loaders->setAccessible(true);
		
		$subject = $reflectionClass->newInstance();
		$subject->addLoader($mockLoader);
		
		self::assertEquals([$mockLoader], $loaders->getValue($subject));
	}
	
	/**
	 * @expectedException \Snuggle\Exceptions\InvalidLoaderException
	 */
	public function test_addLoader_LoaderNotValid_ExceptionThrown()
	{
		$subject = new LoadersCollection();
		$subject->addLoader('testString');
	}
	
	public function test_tryLoad_LoadersEmpty_ReturnNull()
	{
		$subject = new LoadersCollection();
		
		self::assertNull($subject->tryLoad('Test'));
	}
	
	public function test_tryLoad_ConfigLoaded_ReturnArray()
	{
		$mockLoader = $this->getMockBuilder(IConfigLoader::class)->getMock();
		$mockLoader->expects($this->once())->method('tryLoad')->willReturn(['test']);
		
		$subject = new LoadersCollection();
		$subject->addLoader($mockLoader);
		
		self::assertEquals(['test'], $subject->tryLoad('Test'));
	}
	
	public function test_tryLoad_ConfigDidNotLoad_ReturnNull()
	{
		$mockLoader = $this->getMockBuilder(IConfigLoader::class)->getMock();
		$mockLoader->expects($this->once())->method('tryLoad')->willReturn(null);
		
		$subject = new LoadersCollection();
		$subject->addLoader($mockLoader);
		
		self::assertNull($subject->tryLoad('Test'));
	}
}