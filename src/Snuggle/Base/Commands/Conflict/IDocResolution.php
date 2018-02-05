<?php
namespace Snuggle\Base\Commands\Conflict;


use Snuggle\Base\Connection\Response\IRawResponse;


interface IDocResolution
{
	public function ignoreConflict(): void;
	public function overrideConflict(): void;
	public function failOnConflict(): void;
	
	public function setStrategy(string $strategy): void;
	public function execute(IDocConflictableCommand $command): IRawResponse;
}