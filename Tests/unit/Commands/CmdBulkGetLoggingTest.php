<?php
namespace Snuggle\Commands;


use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Snuggle\Base\IConnection;
use Snuggle\Utils\Logging;


/**
 * @group unit
 */
class CmdBulkGetLoggingTest extends TestCase
{
	protected function tearDown(): void
	{
		Logging::setLogger(new NullLogger());
	}

	/**
	 * @dataProvider emptyKeysProvider
	 */
	public function test_keys_EmptyKeysPassed_WarningLogged(?array $keys): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::once())
			->method('warning')
			->with(
				'Empty keys array passed to Snuggle',
				self::callback(fn(array $context): bool =>
					count($context) === 1 &&
					isset($context['exception']) &&
					$context['exception'] instanceof \Exception
				)
			);

		Logging::setLogger($logger);

		(new CmdBulkGet($this->createMock(IConnection::class)))->keys($keys);
	}

	public function test_keys_NonEmptyKeysPassed_WarningNotLogged(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::never())
			->method('warning');

		Logging::setLogger($logger);

		(new CmdBulkGet($this->createMock(IConnection::class)))->keys(['document-id']);
	}

	public static function emptyKeysProvider(): array
	{
		return [
			'null'        => [null],
			'empty array' => [[]]
		];
	}
}
