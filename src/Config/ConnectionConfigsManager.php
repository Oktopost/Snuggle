<?php
namespace Snuggle\Config;


use Snuggle\Exceptions;
use Snuggle\SnuggleScope;


class ConnectionConfigsManager
{
	/** @var LoadersCollection */
	private $loaders = null;
	
	/** @var ConnectionConfig[] */
	private $configs = [];
	
	
	private function tryLoad(string $name): bool
	{
		if (!$this->loaders)
			return false;
		
		$config = $this->loaders->tryLoad($name);
		
		if (is_null($config))
			return false;
		
		$config = ConnectionConfig::create($config);
		$this->configs[$name] = $config;
		
		return true;
	}
	
	
	public function has(string $name): bool
	{
		if (isset($this->configs[$name]))
			return true;
		
		if ($this->tryLoad($name))
			return true;
		
		return false;
	}
	
	public function get(string $name = 'main'): ConnectionConfig
	{
		if (!$this->loaders && !$this->configs && $name == 'main')
			$this->configs['main'] = ConnectionConfig::create([]);
		
		if (!isset($this->configs[$name]) && !$this->tryLoad($name))
			throw new Exceptions\ConfigurationNotFoundException($name);
		
		return $this->configs[$name];
	}
	
	public function addLoaders($loaders): void
	{
		if (!$this->loaders)
			$this->loaders = new LoadersCollection();
		
		$this->loaders->addLoader($loaders);
	}
	
	/**
	 * @param string|array|null $item
	 * @param array|null $data
	 */
	public function add($item, $data = null): void
	{
		$data = $data ?? [];
		
		if (is_null($item))
		{
			$item = 'main';
		}
		else if (is_array($item))
		{
			$data = $item;
			$item = 'main';
		}
		
		if (isset($this->configs[$item]))
		{
			throw new Exceptions\ConfigurationAlreadyDefinedException($item);
		}
		else if (is_array($data))
		{
			$config = ConnectionConfig::create($data);
			$this->configs[$item] = $config;
		}
		else if ($data instanceof ConnectionConfig) 
		{
			$this->configs[$item] = $data;
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
			throw new Exceptions\InvalidConfigFormatException();
		}
	}
}