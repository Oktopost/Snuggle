<?php
namespace Snuggle\Base\Commands;


use Snuggle\Base\ICommand;


interface IQuery extends ICommand
{
	public function queryCode(): int;
	public function queryBool(): bool;
	public function queryBody(): ?string;
	public function queryHeaders(): array;
	public function queryJson($asArray = true);
}