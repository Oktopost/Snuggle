<?php
namespace Snuggle\Factories\Connections;


use Snuggle\Base\IConnection;
use Snuggle\Config\ConnectionConfig;
use Snuggle\Base\Factories\IConnectionFactory;
use Snuggle\Connection\Providers\HttpfullConnection;


class HttpfullFactory implements IConnectionFactory
{
	public function get(ConnectionConfig $config): IConnection
	{
		return new HttpfullConnection($config);
	}
}