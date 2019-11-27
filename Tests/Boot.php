<?php
use Snuggle\CouchDB;
use Snuggle\Base\IConnector;

require_once __DIR__ . '/../vendor/autoload.php';


function createTestDB($name): void
{
	$conn = getSanityConnector();
	
	if ($conn->db()->exists($name))
		return;
	
	getSanityConnector()->db()->create($name);
}

function getSanityConnector(): IConnector
{
	$config = ['host' => '127.0.0.1'];
	
	if (file_exists(__DIR__ . '/auth.ini'))
	{
		$res = parse_ini_file(__DIR__ . '/auth.ini');
		$config = array_merge($config, $res);
	}
	
	$couchDB = new CouchDB();
	$couchDB->config()->addConnection($config);
	
	return $couchDB->connector();
}

function getInvalidSanityConnector(): IConnector
{
	$config = [
		'host' => '127.0.0.1',
		'user' => 'invalid_' . rand(PHP_INT_MIN, PHP_INT_MAX),
		'pass' => 'invalid_' . rand(PHP_INT_MIN, PHP_INT_MAX)
	];
	
	$couchDB = new CouchDB();
	$couchDB->config()->addConnection($config);
	
	return $couchDB->connector();
}