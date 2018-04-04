<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\DB\DBInfo;


interface ICmdDB
{
	public function create(string $name, ?int $shards = null): void;
	public function createIfNotExists(string $name, ?int $shards = null): bool;
	public function exists(string $name): bool;
	public function drop(string $name): void;
	public function dropIfExists(string $name): bool;
	public function info(string $name): DBInfo;
	public function compact(string $name): void;
}