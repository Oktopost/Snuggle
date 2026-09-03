<?php
namespace Snuggle\Connection\Providers;


use Gazelle\Gazelle;
use Gazelle\IConnection;
use Gazelle\IRequestParams;
use Gazelle\IResponse;
use Gazelle\Exceptions\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Snuggle\Connection\Request\RawRequest;
use Snuggle\Exceptions\ServerUnreachableException;
use Snuggle\Exceptions\SnuggleException;
use Snuggle\Utils\Logging;


/**
 * @group unit
 */
class GazelleConnectionLoggingTest extends TestCase
{
	protected function tearDown(): void
	{
		Logging::setLogger(new NullLogger());
	}

	public function test_request_RequestExceptionThrown_CriticalLogged(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::once())
			->method('critical')
			->with(
				'Snuggle Request Timeout',
				self::callback(fn(array $context): bool =>
					$context['Uri'] === '/database' &&
					$context['Params'] === ['since' => 'sequence'] &&
					$context['Body'] === '{"id":"document-id"}' &&
					$context['exception'] instanceof RequestException &&
					!array_key_exists('GraphUpdateHandlersIDs', $context)
				)
			);

		Logging::setLogger($logger);
		$connection = $this->createConnection(new class implements IConnection
		{
			public function request(IRequestParams $requestData): IResponse
			{
				throw new RequestException($requestData, 'Connection failed');
			}
		});
		$request = (new RawRequest('/database'))
			->setQueryParam('since', 'sequence')
			->setBody(['id' => 'document-id']);

		try
		{
			$connection->request($request);
			self::fail('ServerUnreachableException was not thrown');
		}
		catch (ServerUnreachableException $exception)
		{
			self::assertInstanceOf(RequestException::class, $exception->getPrevious());
		}
	}

	public function test_request_UnexpectedExceptionThrown_CriticalLogged(): void
	{
		$sourceException = new \RuntimeException('Unexpected failure');
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::once())
			->method('critical')
			->with(
				'Unexpected Snuggle Request Exception',
				self::callback(fn(array $context): bool =>
					$context === ['Uri' => '/database', 'exception' => $sourceException]
				)
			);

		Logging::setLogger($logger);
		$connection = $this->createConnection(new class($sourceException) implements IConnection
		{
			private \RuntimeException $exception;

			public function __construct(\RuntimeException $exception)
			{
				$this->exception = $exception;
			}

			public function request(IRequestParams $requestData): IResponse
			{
				throw $this->exception;
			}
		});

		try
		{
			$connection->request(new RawRequest('/database'));
			self::fail('SnuggleException was not thrown');
		}
		catch (SnuggleException $exception)
		{
			self::assertSame($sourceException, $exception->getPrevious());
		}
	}

	private function createConnection(IConnection $connection): GazelleConnection
	{
		$gazelle = new Gazelle();
		$gazelle->setConnection($connection);

		return new GazelleConnection($gazelle);
	}
}
