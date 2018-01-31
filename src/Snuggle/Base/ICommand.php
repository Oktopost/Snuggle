<?php
namespace Snuggle\Base;


interface ICommand
{
	public function __clone();
	public function setConnection(IConnection $connection): void;
}