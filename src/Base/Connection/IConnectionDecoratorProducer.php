<?php
namespace Snuggle\Base\Connection;


use Snuggle\Base\IConnection;


interface IConnectionDecoratorProducer
{
	public function get(?IConnection $child = null): IConnectionDecorator;
}