<?php
namespace Snuggle;


use Snuggle\Base\IConfig;
use Snuggle\Base\IConnector;


class CouchDB
{
	/** @var Config */
	private $config;
	
	
	public function __construct()
	{
		$this->config = new Config();
	}
	
	
	public function config(): IConfig
	{
		return $this->config;
	}
	
	public function connector(string $name = 'main'): IConnector
	{
		$connection		= $this->config->getConnection($name);
		$commandFactory	= $this->config->getCommandFactory();
		
		return new Connector(
			$commandFactory,
			$connection
		);
	}
}