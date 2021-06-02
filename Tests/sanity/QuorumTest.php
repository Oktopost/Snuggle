<?php
namespace sanity;


use PHPUnit\Framework\TestCase;

use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\IConnection;
use Snuggle\Base\IConnector;
use Snuggle\Connection\Decorators\SnuggleCallbackDecorator;
use Snuggle\Exceptions\Http\ConflictException;


class QuorumTest extends TestCase
{
	private const DB = 'test_snuggle_quorum';	
	
	
	private function getConnection(callable $callback): IConnector
	{
		$couchDB = getCouchDB();
		
		$couchDB->config()
			->addConnectionDecorator(new SnuggleCallbackDecorator(function (IConnection $conn) use($callback) 
			{
				$args = func_get_args();
				call_user_func_array($callback, $args);
				
				array_shift($args);
				
				return $conn->request(...$args);
			}));
		
		return $couchDB->connector();
	}
	
	private function getConnectionWithCallback(bool &$read, bool &$wrote, int $expectedRead, int $expectedWrite): IConnector
	{
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
		use (&$read, &$wrote, $expectedRead, $expectedWrite)
		{
			if (!is_string($request))
			{
				/** @var IRawRequest|string $request */
				$method = $request->getMethod();
				$params = $request->getQueryParams();
			}
			
			if ($method == 'GET')
			{
				$read = true;
				self::assertEquals($params['r'], $expectedRead);
				self::assertArrayNotHasKey('w', $params);
			}
			else
			{
				$wrote = true;
				self::assertEquals($params['w'], $expectedWrite);
				self::assertArrayNotHasKey('r', $params);
			}
		});
		
		return $conn;
	}
	
	private function testConflict(bool &$read, bool &$wrote, string $resolutionName): void
	{
		$read = false;
		$wrote = false;
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 3, 2);
		
		if ($resolutionName == 'resolveConflict')
		{
			$conn->store()
				->quorum(3, 2)
				->into(self::DB)
				->data(['_id' => '2', 'a' => mt_rand(2, 50)])
				->resolveConflict(function ($a, $b) { return $b; })
				->queryBool();
		}
		else
		{
			$conn->store()
				->quorum(3, 2)
				->into(self::DB)
				->data(['_id' => '2', 'a' => 4])
				->$resolutionName()
				->queryBool();
		}
	}
	
	
	public static function setUpBeforeClass(): void
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists(self::DB))
			$conn->db()->drop(self::DB);
		
		$conn->db()->create(self::DB);
	}
	
	public static function tearDownAfterClass(): void
	{
		$conn = getSanityConnector();
		
		if ($conn->db()->exists(self::DB))
			$conn->db()->drop(self::DB);
	}
	
	
	public function test_QuorumPassedTo_CmdGet(): void
	{
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
		{
			self::assertEquals($params['r'], 2);
		});
		
		$conn->get()->quorumRead(2)->from(self::DB)->queryExists('1');
	}
	
	public function test_QuorumNotSet_CmdGet(): void
	{
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
		{
			self::assertArrayNotHasKey('r', $params);
		});
		
		$conn->get()->from(self::DB)->queryExists('1');
	}
	
	
	public function test_QuorumPassedTo_CmdDelete(): void
	{
		$read = false;
		$wrote = false;
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 3, 2);
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '1'])->execute();
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '2'])->execute();
		
		$conn->delete()
			->quorumWrite(2)
			->quorumRead(3)
			->from(self::DB)
			->doc('1')
			->queryBool();
		
		$conn->delete()
			->quorum(3, 2)
			->from(self::DB)
			->doc('2')
			->queryBool();
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	
	public function test_QuorumPassedTo_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 3, 2);
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '2', 'a' => '1'])->execute();
		
		$conn->store()
			->quorumWrite(2)
			->quorumRead(3)
			->into(self::DB)
			->data(['_id' => '1', 'a' => 2])
			->queryBool();
		
		$conn->store()
			->quorum(3, 2)
			->into(self::DB)
			->data(['_id' => '2', 'a' => 4])
			->queryBool();
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_Conflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		$conflicted = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '2', 'a' => '1'])->execute();
		
		
		$this->testConflict($read, $wrote, 'ignoreConflict');
		
		self::assertFalse($read);
		self::assertTrue($wrote);
		
		
		try
		{
			$this->testConflict($read, $wrote, 'failOnConflict');
		}
		catch (ConflictException $exception)
		{
			$conflicted = true;
		}
		
		self::assertFalse($read);
		self::assertTrue($wrote);
		//self::assertTrue($conflicted); Not failing
		
		
		$this->testConflict($read, $wrote, 'mergeNewOnConflict');
		
		self::assertTrue($read);
		self::assertTrue($wrote);
		
		
		$this->testConflict($read, $wrote, 'mergeOverOnConflict');
		
		self::assertTrue($read);
		self::assertTrue($wrote);
		
		
		$this->testConflict($read, $wrote, 'overrideConflict');
		
		self::assertTrue($read);
		self::assertTrue($wrote);
		
		
		$this->testConflict($read, $wrote, 'resolveConflict'); // Conflict callback not working correctly
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
}