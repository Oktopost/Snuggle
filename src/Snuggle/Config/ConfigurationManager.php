<?php
namespace Snuggle\Config;


use Snuggle\SnuggleScope;
use Snuggle\Exceptions\InvalidConfigException;
use Snuggle\Exceptions\ConfigurationNotFoundException;


class ConfigurationManager
{
	/** @var LoadersCollection */
	private $loaders = null;
	
	/** @var ConfigCollection */
	private $configs;
	
	
	private function tryLoad(string $name): bool
	{
		if (!$this->loaders)
			return false;
		
		$config = $this->loaders->tryLoad($name);
		
		if (is_null($config))
			return false;
		
		$config = ConnectionConfig::create($config);
		$this->configs->set($name, $config);
		
		return true;
	}
	
	
	public function __construct()
	{
		$this->configs = new ConfigCollection();
	}
	
	
	public function has(string $name): bool
	{
		if ($this->configs->has($name))
			return true;
		
		if ($this->tryLoad($name))
			return true;
		
		return false;
	}
	
	public function get(string $name = 'main'): ConnectionConfig
	{
		if (!isset($this->configs[$name]) && !$this->tryLoad($name))
			throw new ConfigurationNotFoundException($name);
		
		return $this->configs[$name];
	}
	
	public function addLoaders($loaders): void
	{
		if (!$this->loaders)
			$this->loaders = new LoadersCollection();
		
		$this->loaders->addLoader($loaders);
	}
	
	/**
	 * @param $item
	 * @param null $data
	 */
	public function add($item, $data = null): void
	{
		if (!$data)
		{
			$data = $item;
			$item = 'main';
		}
		
		if (is_array($data))
		{
			$config = ConnectionConfig::create($data);
			$this->configs->set($item, $config);
		}
		else if ($data instanceof ConnectionConfig) 
		{
			$this->configs->set($item, $data);
		}
		else if (interface_exists($data))
		{
			$this->add($item, SnuggleScope::skeleton($data));
		}
		else if (class_exists($data))
		{
			$this->add($item, SnuggleScope::skeleton()->load($data));
		}
		else	
		{
			throw new InvalidConfigException();
		}
	}
}