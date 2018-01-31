<?php
namespace Snuggle\Factories\Connections;


use Snuggle\Base\IConnection;
use Snuggle\Base\Factories\IConnectionFactory;
use Snuggle\Config\ConnectionConfig;


class HttpfullFactory implements IConnectionFactory
{
	public function get(ConnectionConfig $config): IConnection
	{
		return null;
	}
}