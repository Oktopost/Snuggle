<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\DB\DBInfo;
use Snuggle\Core\DB\DDocInfo;


interface ICmdDB
{
	public function create(string $name, ?int $shards = null): void;
	public function createIfNotExists(string $name, ?int $shards = null): bool;
	public function exists(string $name): bool;
	public function drop(string $name): void;
	public function dropIfExists(string $name): bool;
	public function info(string $name): DBInfo;
	public function designDocInfo(string $dbName, string $dDocName): DDocInfo;
	public function designDocs(string $dbName): array;
	public function compact(string $name, ?string $design = null): void;
	public function setRevisionsLimit(string $name, int $limit): void;
}