<?php
namespace Snuggle\Config;


use PHPUnit\Framework\TestCase;


class ConfigParserTest extends TestCase
{
	public function test_parse_UserNotSet_ReturnWithoutCredentials()
	{
		self::assertEquals([
			'URI'		=> 'localhost',
			'Protocol'	=> 'http',
			'Port'		=> 5984,
			'Generic'	=> []
		], ConfigParser::parse([]));
	}
}