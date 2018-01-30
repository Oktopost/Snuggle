<?php
namespace Snuggle\Config;


use Traitor\TStaticClass;


class ConfigParser
{
	use TStaticClass;
	
	
	private const DEFAULT_PORT		= 5986;
	private const DEFAULT_HOST		= 'localhost';
	private const DEFAULT_PROTOCOL	= 'http';
	
	
	private static function fixKeys(array $data): array
	{
		$result = [];
		
		foreach ($data as $key => $value)
		{
			$result[strtolower(trim($key))] = $value;
		}
		
		return $result;
	}
	
	private static function getCredentials(array $data): array
	{
		$user = $data['user'] ?? $data['username'] ?? null;
		
		if (!$user)
			return [];
		
		$pass = $data['pass'] ?? $data['password'] ?? null;
		
		return [
			'User'		=> $user,
			'Password'	=> $pass
		];
	}
	
	private static function getURL(array $data): array
	{
		$uri		= $data['host'] ?? $data['uri'] ?? $data['url'] ?? $data['address'] ?? self::DEFAULT_HOST; 
		$port		= $data['port'] ?? self::DEFAULT_PORT;
		$protocol	= $data['protocol'] ?? self::DEFAULT_PROTOCOL;
		
		return [
			'URI'		=> $uri,
			'Protocol'	=> $protocol,
			'Port'		=> $port
		];
	}
	
	
	public static function parse(array $data): array
	{
		$data = self::fixKeys($data);
		
		return array_merge
			(
				self::getURL($data),
				self::getCredentials($data)
			);
	}
}