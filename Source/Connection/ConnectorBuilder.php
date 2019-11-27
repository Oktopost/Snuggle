<?php
namespace Snuggle\Connection;


use Snuggle\Base\Connection\IConnectionDecorator;
use Snuggle\Base\Connection\IConnectionDecoratorProducer;
use Snuggle\Base\IConnection;
use Snuggle\Config;
use Snuggle\Base\IConnector;
use Snuggle\Connection\Decorators\ErrorHandler;
use Snuggle\SnuggleScope;


class ConnectorBuilder
{
	/** @var Config */
	private $config;
	
	
	private function decorate(IConnection $connection): IConnection
	{
		$connection = new ErrorHandler($connection);
		
		foreach ($this->config->getDecorators() as $decorator)
		{
			if (is_string($decorator))
			{
				$decorator = SnuggleScope::skeleton()->load($decorator);
			}
			
			if ($decorator instanceof IConnectionDecoratorProducer)
			{
				$connection = $decorator->get($connection);
			}
			else if ($decorator instanceof IConnectionDecorator)
			{
				$decorator->setChild($connection);
				$connection = $decorator;
			}
		}
		
		return $connection;
	}
	
	
	public function __construct(Config $config)
	{
		$this->config = $config;
	}
	
	
	public function getConnection(string $name): IConnector
	{
		$connection		= $this->config->getConnection($name);
		$commandFactory	= $this->config->getCommandFactory();
		
		$connection = $this->decorate($connection);
		
		return new Connector(
			$commandFactory,
			$connection
		);
	}
}