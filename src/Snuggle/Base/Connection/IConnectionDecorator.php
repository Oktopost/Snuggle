<?php
namespace Snuggle\Base\Connection;


use Snuggle\Base\IConnection;


interface IConnectionDecorator extends IConnection
{
	public function setChild(IConnection $connection): void;
}