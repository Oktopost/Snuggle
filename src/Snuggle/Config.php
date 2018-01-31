<?php
namespace Snuggle;


use Snuggle\Base\IConfig;
use Snuggle\Base\IConnection;
use Snuggle\Base\Factories\ICommandFactory;
use Snuggle\Base\Factories\IConnectionFactory;
use Snuggle\Config\IConfigLoader;
use Snuggle\Config\ConnectionConfig;
use Snuggle\Config\ConnectionConfigsManager;
use Snuggle\Factories\Commands\SimpleFactory;
use Snuggle\Factories\Connections\HttpfullFactory;


class Config implements IConfig
{
	/** @var ConnectionConfigsManager */
	private $connectionsManager;
	
	/** @var ICommandFactory */
	private $commandFactory	= null;
	
	/** @var IConnectionFactory */
	private $connectionFactory = null;
	
	
	public function __construct()
	{
		$this->connectionsManager = new ConnectionConfigsManager();
	}
	
	
	public function getConnectionConfig(string $name): ConnectionConfig
	{
		return $this->connectionsManager->get($name);
	}
	
	public function getCommandFactory(): ICommandFactory
	{
		if (!$this->commandFactory)
			$this->commandFactory = new SimpleFactory();
		
		return $this->commandFactory;
	}
	
	public function getConnectionFactory(): IConnectionFactory
	{
		if (!$this->connectionFactory)
			$this->connectionFactory = new HttpfullFactory();
		
		return $this->connectionFactory;
	}
	
	public function getConnection(string $name): IConnection
	{
		$config = $this->connectionsManager->get($name);
		return $this->getConnectionFactory()->get($config);
	}
	
	
	/**
	 * @param string|array $item Name of the connection. If set to array, name will be 'main'
	 * @param array|null $data
	 * @return IConfig|static
	 */
	public function addConnection($item, ?array $data = null): IConfig
	{
		$this->connectionsManager->add($item, $data);
		return $this;
	}
	
	/**
	 * @param IConfigLoader|string|array $loader IConfigLoader instance or class name.
	 * @return IConfig
	 */
	public function addLoader($loader): IConfig
	{
		$this->connectionsManager->addLoaders($loader);
		return $this;
	}
	
	/**
	 * @param ICommandFactory $factory
	 * @return IConfig
	 */
	public function setCommandFactory(ICommandFactory $factory): IConfig
	{
		$this->commandFactory = $factory;
		return $this;
	}
	
	/**
	 * @param IConnectionFactory $factory
	 * @return IConfig|static
	 */
	public function setConnectionFactory(IConnectionFactory $factory): IConfig
	{
		$this->connectionFactory = $factory;
		return $this;
	}
}