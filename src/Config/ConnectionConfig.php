<?php
namespace Snuggle\Config;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string $Protocol
 * @property string $URI
 * @property int	$Port
 * @property string $User
 * @property string $Password
 */
class ConnectionConfig extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Protocol'	=> LiteSetup::createString('http'),
			'URI'		=> LiteSetup::createString('localhost'),
			'Port'		=> LiteSetup::createInt(5984),
			'User'		=> LiteSetup::createString(null),
			'Password'	=> LiteSetup::createString(null),
		];
	}
	
	
	public function __debugInfo()
	{
		$result = parent::__debugInfo();
		
		if ($result['Password'])
			$result['Password'] = '********';
		
		return $result;
	}
	
	
	public function setCredentials(string $user, string $password):void
	{
		$this->User		= $user;
		$this->Password	= $password;
	}
	
	public function hasCredentials(): bool
	{
		return (bool)$this->User;
	}
	
	public function isHttp(): bool
	{
		return $this->Protocol == 'http';
	}
	
	public function isHttps(): bool
	{
		return $this->Protocol == 'https';
	}
	
	public function getURL(): string
	{
		return "{$this->Protocol}://{$this->URI}:{$this->Port}";
	}
	
	
	public static function create(array $config): ConnectionConfig
	{
		$config = ConfigParser::parse($config);
		
		$object = new ConnectionConfig();
		$object->fromArray($config);
		
		return $object;
	}
}