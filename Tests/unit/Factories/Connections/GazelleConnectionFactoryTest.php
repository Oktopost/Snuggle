<?php
namespace Snuggle\Factories\Connections;


use Gazelle\IResponse;
use Gazelle\IConnection;
use Gazelle\IRequestParams;
use Gazelle\IRequestMetaData;
use Gazelle\IConnectionDecorator;
use Snuggle\Config\ConnectionConfig;

use PHPUnit\Framework\TestCase;


class DummyResponse implements IResponse
{
	public function getRequestParams(): IRequestParams {}
	public function requestMetaData(): IRequestMetaData {}
	public function getCode(): int { return 200; }
	public function getHeaders(): array { return []; }
	public function getHeader(string $key, bool $firstValue = true): ?string {}
	public function hasHeader(string $key): bool {}
	public function hasBody(): bool { return false; }
	public function bodyLength(): int {}
	public function getBody(): string { return ''; }
	public function getJSON(): array {}
	public function tryGetJSON(): ?array {}
	public function isSuccessful(): bool {}
	public function isComplete(): bool {}
	public function isRedirect(): bool {}
	public function isFailed(): bool {}
	public function isServerError(): bool {}
	public function isClientError(): bool {}
}


/**
 * @group unit
 */
class GazelleConnectionFactoryTest extends TestCase
{
	public function test_GazzelleDecoratorConfigured_GazelleDecoratorUsed()
	{
		$factory = new GazelleConnectionFactory();
		$decorator =
			new class implements IConnectionDecorator 
			{
				public bool $isCalled = false;
				public function setChild(IConnection $connection): void {}
				public function request(IRequestParams $requestData): IResponse
				{
					$this->isCalled = true;
					return new DummyResponse();
				}
			};
		
		$conn = $factory->get(ConnectionConfig::create(['generic' => ['GazelleDecorator' => $decorator]]));
		
		
		$conn->request("a", "b", []);
		
		
		self::assertTrue($decorator->isCalled);
	}
}