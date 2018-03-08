<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Server\Index;


interface ICmdServer
{
	public function databases(): array;
	public function info(): Index;
	public function UUIDs(int $count = 20): array;
}