<?php
namespace Snuggle\Factories\Connections;


use Gazelle\Gazelle;
use Snuggle\Base\IConnection;
use Snuggle\Base\Factories\IConnectionFactory;
use Snuggle\Config\ConnectionConfig;
use Snuggle\Connection\Providers\GazelleConnection;


class GazelleConnectionFactory implements IConnectionFactory
{
	private $configCallback;
	private array $connections = [];
	
	
	private function store(Gazelle $gazelle, string $key): void
	{
		if (count($this->connections) > 32)
		{
			unset($this->connections[random_int(0, count($this->connections) - 1)]);
			$this->connections = array_values($this->connections);
		}
		
		$this->connections[$key] = $gazelle;
	}
	
	private function getKey(ConnectionConfig $config): string
	{
		return sha1(jsonencode($config->toArray()));
	}
	
	private function createConnectorForConfig(ConnectionConfig $config): Gazelle
	{
		$gazelle = new Gazelle();
		$this->configGazelle($gazelle, $config);
		return $gazelle;
	}
	
	private function setDecorator(Gazelle $gazelle, ConnectionConfig $config): void
	{
		$decorator = $config->Generic['GazelleDecorator'] ?? null;
		
		if (!$decorator)
			return;
		
		$gazelle->addDecorator($decorator);
	}
	
	
	protected function configGazelle(Gazelle $gazelle, ConnectionConfig $config): void
	{
		$template = $gazelle->template();
		
		$template
			->setURL($config->getURL())
			->setMaxRedirects(0)
			->setURL($config->getURL())
			->setExecutionTimeout(30, 30);
			
		if ($config->hasCredentials())
		{
			$template->setCurlOption(
				CURLOPT_USERPWD, 
				"{$config->User}:{$config->Password}");
		}
		
		$this->setDecorator($gazelle, $config);
		
		if ($this->configCallback)
		{
			$callback = $this->configCallback;
			$callback($gazelle, $config);
		}
	}
	
	
	public function __construct(?callable $configCallback = null)
	{
		$this->configCallback = $configCallback;
	}


	public function get(ConnectionConfig $config): IConnection
	{
		$key = $this->getKey($config);
		
		if (isset($this->connections[$key]))
		{
			$gazelle = $this->connections[$key];
		}
		else
		{
			$gazelle = $this->createConnectorForConfig($config);
			$this->store($gazelle, $key);
		}
		
		return new GazelleConnection($gazelle);
	}
}