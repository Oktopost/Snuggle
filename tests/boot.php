<?php
use Snuggle\CouchDB;
use Snuggle\Base\IConnector;

require_once __DIR__ . '/../vendor/autoload.php';


function getSanityConnector(): IConnector
{
	$couchDB = new CouchDB();
	$couchDB->config()
		->addConnection(['host' => '127.0.0.1']);
	
	return $couchDB->connector();
}
