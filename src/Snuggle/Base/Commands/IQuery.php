<?php
namespace Snuggle\Base\Commands;


interface IQuery
{
	public function queryCode(): int;
	public function queryBody(): ?string;
	public function queryHeaders(): array;
	public function queryJson($asArray = true);
}