<?php
namespace sanity;


use PHPUnit\Framework\TestCase;

use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Base\IConnection;
use Snuggle\Base\IConnector;
use Snuggle\Connection\Decorators\SnuggleCallbackDecorator;
use Snuggle\Exceptions\Http\ConflictException;
use Structura\Strings;


/**
 * @group integration
 */
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
		$conn = $this->getConnection(
			function(IConnection $conn, $request, $method, array $params)
				use (&$read, &$wrote, $expectedRead, $expectedWrite)
			{
				if (!is_string($request))
				{
					/** @var IRawRequest|string $request */
					$method = $request->getMethod();
					$params = $request->getQueryParams();
					$request = $request->getURI();
				}
				
				if ($method == 'GET' || Strings::contains($request, '_all_docs'))
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
	
	private function assertConflict(bool &$read, bool &$wrote, string $resolutionName, bool $isBulk = false, ?string $id = null): void
	{
		$conn = $this->getConnectionWithCallback($read, $wrote, 3, 2);
		$id = $id ?? '2';
		
		if ($isBulk)
		{
			if ($resolutionName == 'resolveConflict')
			{
				$conn->storeAll()
					->quorum(3, 2)
					->into(self::DB)
					->dataSet([['_id' => $id, 'a' => mt_rand(2, 50)]])
					->resolveConflict(
						function ($a, $b) 
						{ 
							return $b; 
						})
					->execute();
			}
			else
			{
				$conn->storeAll()
					->quorum(3, 2)
					->into(self::DB)
					->dataSet([['_id' => $id, 'a' => mt_rand(2, 50)]])
					->$resolutionName()
					->execute();
			}
		}
		else
		{
			if ($resolutionName == 'resolveConflict')
			{
				$conn->store()
					->quorum(3, 2)
					->into(self::DB)
					->data(['_id' => $id, 'a' => mt_rand(2, 50)])
					->resolveConflict(
						function ($a, $b) 
						{ 
							return $b; 
						})
					->execute();
			}
			else
			{
				$conn->store()
					->quorum(3, 2)
					->into(self::DB)
					->data(['_id' => $id, 'a' => mt_rand(2, 50)])
					->$resolutionName()
					->execute();
			}
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
	
	public function test_QuorumPassedTo_CmdBulkGet(): void
	{
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => '1'])->execute();
		
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
		{
			if (!is_string($request))
			{
				/** @var IRawRequest $request */
				$params = $request->getQueryParams();
			}
			
			self::assertEquals($params['r'], 2);
		});
		
		
		$conn->getAll()
			->quorumRead(2)
			->from(self::DB)
			->keys(['1'])
			->queryDocs();
	}
	
	public function test_QuorumNotSet_CmdBulkGet(): void
	{
		$conn = $this->getConnection(function (IConnection $conn, $request, $method, array $params)
		{
			if (!is_string($request))
			{
				/** @var IRawRequest $request */
				$params = $request->getQueryParams();
			}
			
			self::assertArrayNotHasKey('r', $params);
		});
		
		$conn->getAll()->from(self::DB)->queryDocs();
	}
	
	
	public function test_QuorumPassedTo_CmdDelete(): void
	{
		$read = false;
		$wrote = false;
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 3, 2);
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__ . '1'])->execute();
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__ . '2'])->execute();
		
		$conn->delete()
			->quorumWrite(2)
			->quorumRead(3)
			->from(self::DB)
			->doc(__FUNCTION__ . '1')
			->queryBool();
		
		$conn->delete()
			->quorum(3, 2)
			->from(self::DB)
			->doc(__FUNCTION__ . '2')
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
	
	public function test_QuorumPassedTo_IgnoreConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__, 'a' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'ignoreConflict', false, __FUNCTION__);
		
		self::assertFalse($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_FailOnConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		$conflicted = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		try
		{
			$this->assertConflict($read, $wrote, 'failOnConflict', false, __FUNCTION__);
		}
		catch (ConflictException $exception)
		{
			$conflicted = true;
		}
		
		self::assertFalse($read);
		self::assertTrue($wrote);
		self::assertTrue($conflicted);
	}
	
	public function test_QuorumPassedTo_MergeNewOnConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)
			->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'mergeNewOnConflict', false, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_MergeOverOnConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(
			['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'mergeOverOnConflict', false, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_OverrideConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'overrideConflict', false, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_ResolveConflict_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'resolveConflict', false, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_IgnoreConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'ignoreConflict', true, __FUNCTION__);
		
		self::assertFalse($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_FailOnConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		$conflicted = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		try
		{
			$this->assertConflict($read, $wrote, 'failOnConflict', true, __FUNCTION__);
		}
		catch (ConflictException $exception)
		{
			$conflicted = true;
		}
		
		self::assertFalse($read);
		self::assertTrue($wrote);
		self::assertTrue($conflicted);
	}
	
	public function test_QuorumPassedTo_MergeNewOnConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'mergeNewOnConflict', true, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_MergeOverOnConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'mergeOverOnConflict', true, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_OverrideConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'overrideConflict', true, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_ResolveConflict_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$this->assertConflict($read, $wrote, 'resolveConflict', true, __FUNCTION__);
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	
	public function test_QuorumPassedTo_ReadWriteSetters_CmdStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 2, 3);
		
		$conn->store()
			->quorumRead(2)
			->quorumWrite(3)
			->into(self::DB)
			->data(['_id' => __FUNCTION__, 'a' => mt_rand(2, 50)])
			->overrideConflict()
			->queryBool();
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
	
	public function test_QuorumPassedTo_ReadWriteSetters_CmdBulkStore(): void
	{
		$read = false;
		$wrote = false;
		
		getSanityConnector()->insert()->into(self::DB)->data(['_id' => __FUNCTION__])->execute();
		
		$conn = $this->getConnectionWithCallback($read, $wrote, 2, 3);
		
		$conn->storeAll()
			->quorumRead(2)
			->quorumWrite(3)
			->into(self::DB)
			->dataSet([['_id' => __FUNCTION__, 'a' => mt_rand(2, 50)]])
			->mergeNewOnConflict()
			->execute();
		
		self::assertTrue($read);
		self::assertTrue($wrote);
	}
}