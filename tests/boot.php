<?php
use Snuggle\CouchDB;
use Snuggle\Base\IConnector;

require_once __DIR__ . '/../vendor/autoload.php';


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
