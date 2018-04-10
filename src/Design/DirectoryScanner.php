<?php
namespace Snuggle\Design;


use Snuggle\Conflict\RecursiveMerge;
use Snuggle\Exceptions\FatalSnuggleException;
use Traitor\TStaticClass;


class DirectoryScanner
{
	use TStaticClass;
	
	
	private const MAP = [
		'views' => 'views',
		'view'	=> 'views'
	];
	
	
	private static function getRealPath(string $path): string
	{
		$fullPath = realpath($path);
		
		if ($fullPath === false)
			throw new FatalSnuggleException("Path '$path' does not exist");
		
		return $fullPath;
	}
	
	private static function scanSubDir(string $type, string $path, string $filter = '*'): array
	{
		$data = [];
		
		foreach (glob($path . '/*') as $inner)
		{
			$name = pathinfo($inner)['filename'];
			
			if (is_file($inner) && is_readable($inner) && fnmatch($filter, $inner))
			{
				$content = file_get_contents($inner);
				
				$data[$type][$name] = ($type == 'views' ?
					['map' => $content] : 
					$content);
			}
			else if ($type == 'views' && file_exists($inner . '/map.js'))
			{
				$content = ['map' => file_get_contents($inner . '/map.js')];
				
				if (file_exists($inner . '/reduce.js'))
					$content['reduce'] =  file_get_contents($inner . '/reduce.js');
				
				$data[$type][$name] = $content;
			}
		}
		
		return $data;
	}
	
	
	public static function scanViewsDir(string $path, string $filter = '*'): array
	{
		return self::scanSubDir('views', self::getRealPath($path), $filter);
	}
	
	public static function scanDir(string $path, string $filter = '*'): array
	{
		$data = [];
		$path = self::getRealPath($path);
		
		foreach (glob($path . '/*', GLOB_ONLYDIR) as $item)
		{
			$type = self::MAP[basename($item)] ?? null;
			
			if (!$type)
				continue;
			
			$data = RecursiveMerge::merge(
				$data, 
				self::scanSubDir($type, $item, $filter)
			);
		}
		
		return $data;
	}
}