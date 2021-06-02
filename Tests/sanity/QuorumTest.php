<?php
namespace sanity;


use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\IConnection;
use Snuggle\Base\IConnector;
use Snuggle\Connection\Decorators\SnuggleCallbackDecorator;


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
		$readReachedCount = 0;
		$writeReachedCount = 0;
		
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
			use (&$readReachedCount, &$writeReachedCount)
		{
			if (!is_string($request))
			{
				/** @var IRawRequest|string $request */
				$method = $request->getMethod();
				$params = $request->getQueryParams();
			}
			
			if ($method == 'GET')
			{
				$readReachedCount++;
				self::assertEquals($params['r'], 3);
				self::assertArrayNotHasKey('w', $params);
			}
			else
			{
				$writeReachedCount++;
				self::assertEquals($params['w'], 2);
				self::assertArrayNotHasKey('r', $params);
			}
		});
		
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
		
		self::assertEquals($readReachedCount, 2);
		self::assertEquals($writeReachedCount, 2);
	}
	
	
	public function test_QuorumPassedTo_CmdStore(): void
	{
		$readReachedCount = 0;
		$writeReachedCount = 0;
		
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
			use (&$readReachedCount, &$writeReachedCount)
		{
			if (!is_string($request))
			{
				/** @var IRawRequest|string $request */
				$method = $request->getMethod();
				$params = $request->getQueryParams();
			}
			
			if ($method == 'GET')
			{
				$readReachedCount++;
				self::assertEquals($params['r'], 3);
				self::assertArrayNotHasKey('w', $params);
			}
			else
			{
				$writeReachedCount++;
				self::assertEquals($params['w'], 2);
				self::assertArrayNotHasKey('r', $params);
			}
		});
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '2', 'a' => '1'])->execute();
		
		$conn->store()
			->quorumWrite(2)
			->quorumRead(3)
			->into(self::DB)
			->data(['_id' => '1', 'a' => 2])
			->queryBool();
		
		$conn->store()
			->quorum(3, 2)
			->from(self::DB)
			->data(['_id' => '2', 'a' => 4])
			->queryBool();
		
		self::assertEquals($readReachedCount, 2);
		self::assertEquals($writeReachedCount, 2);
	}
	
	
}