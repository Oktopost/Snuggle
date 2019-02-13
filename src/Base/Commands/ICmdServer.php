<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Server\ActiveTask;
use Snuggle\Core\Server\Index;


interface ICmdServer
{
	public function databases(): array;
	public function info(): Index;
	
	/**
	 * @param string|null $type
	 * @return array|ActiveTask[]
	 */
	public function activeTasks(?string $type = null): array;
	
	public function UUIDs(int $count = 20): array;
}