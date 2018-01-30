<?php
namespace Snuggle\Config;


use Structura\Map;

class ConfigCollection
{
	/** @var ConnectionConfig[] */
	private $map = [];
	
	
	public function set(string $name, ConnectionConfig $config): void
	{
		$this->map[$name] = $config;
	}
	
	public function has(string $name): bool
	{
		return isset($this->map[$name]);
	}
	
	public function get(string $name): ?ConnectionConfig
	{
		return $this->map[$name] ?? null;
	}
}