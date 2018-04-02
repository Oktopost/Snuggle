<?php

namespace Snuggle\Config;


interface IConfigLoader
{
	public function tryLoad(string $name): ?array;
}