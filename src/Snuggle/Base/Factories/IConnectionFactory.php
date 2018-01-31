<?php
namespace Snuggle\Base\Factories;


use Snuggle\Base\IConnection;
use Snuggle\Config\ConnectionConfig;


interface IConnectionFactory
{
	public function get(ConnectionConfig $config): IConnection; 
}