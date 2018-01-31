<?php
namespace Snuggle\Base\Connection\Response;


interface IBody
{
	public function isEmpty(): bool;
	public function length(): int;
	public function getString(): string;
	public function getJson($asArray = false);
}