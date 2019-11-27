<?php
namespace Snuggle;


use Snuggle\Base\IConfig;
use Snuggle\Base\IConnector;
use Snuggle\Connection\ConnectorBuilder;


class CouchDB
{
	/** @var Config */
	private $config;
	
	/** @var ConnectorBuilder */
	private $connectorBuilder;
	
	
	public function __construct()
	{
		$this->config = new Config();
		$this->connectorBuilder = new ConnectorBuilder($this->config);
	}
	
	
	public function config(): IConfig
	{
		return $this->config;
	}
	
	public function connector(string $name = 'main'): IConnector
	{
		return $this->connectorBuilder->getConnection($name);
	}
}