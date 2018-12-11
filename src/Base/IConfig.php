<?php
namespace Snuggle\Base;


use Snuggle\Base\Factories\ICommandFactory;
use Snuggle\Base\Factories\IConnectionFactory;
use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\IConnectionDecoratorProducer;
use Snuggle\Config\IConfigLoader;


interface IConfig
{
	/**
	 * @param string|array|null $item Name of the connection. If set to array, name will be 'main'
	 * @param array|null $data
	 * @return IConfig|static
	 */
	public function addConnection($item, ?array $data = null): IConfig;
	
	/**
	 * @param IConfigLoader|string|array $loader IConfigLoader instance or class name.
	 * @return IConfig|static
	 */
	public function addLoader($loader): IConfig;
	
	/**
	 * @param IConnectionDecorator|IConnectionDecoratorProducer|string|array $decorator
	 * @return IConfig
	 */
	public function addConnectionDecorator(...$decorator): IConfig;
	
	/**
	 * @param ICommandFactory $factory
	 * @return IConfig|static
	 */
	public function setCommandFactory(ICommandFactory $factory): IConfig;
	
	/**
	 * @param IConnectionFactory $factory
	 * @return IConfig|static
	 */
	public function setConnectionFactory(IConnectionFactory $factory): IConfig;
}