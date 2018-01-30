<?php
namespace Snuggle\Config;


use Snuggle\SnuggleScope;
use Snuggle\Exceptions\SnuggleException;
use Snuggle\Exceptions\InvalidLoaderException;


class LoadersCollection
{
	/** @var IConfigLoader[] */
	private $loaders = [];
	
	
	/**
	 * @param IConfigLoader|string|array $loader
	 * @throws SnuggleException
	 */
	public function addLoader($loader): void
	{
		if (is_array($loader))
		{
			foreach ($loader as $item)
			{
				$this->addLoader($item);
			}
			return;
		}
		else if (is_string($loader))
		{
			if (class_exists($loader))
			{
				$this->addLoader(SnuggleScope::skeleton()->load($loader));
				return;
			}
			else if (interface_exists($loader))
			{
				$this->addLoader(SnuggleScope::skeleton($loader));
				return;
			}
		}
		else if ($loader instanceof IConfigLoader)
		{
			$this->loaders[] = $loader;
			return;
		}
		
		throw new InvalidLoaderException();
	}
	
	
	public function tryLoad(string $name): ?array 
	{
		foreach ($this->loaders as $loader)
		{
			$result = $loader->tryLoad($name);
			
			if ($result)
				return $result;
		}
		
		return null;
	}
}