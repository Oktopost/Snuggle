<?php
namespace Snuggle\Commands\Conflict;


use Snuggle\Base\Commands\ISingleDoc;


interface ISingleDocCommand extends ISingleDoc
{
	public function getDocID(): string;
	public function getDB(): string;
}