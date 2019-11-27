<?php
namespace sanity;


use PHPUnit\Framework\TestCase;
use Snuggle\Base\Commands\ICmdBulkGet;
use Snuggle\Commands\CmdDesign;
use Snuggle\Design\DirectoryScanner;
use Snuggle\Exceptions\Http\NotFoundException;


/**
 * @group integration
 */
class CmdDesignTest extends TestCase
{
	private const MAIN_DB	= 'test_snuggle_design_docs_sanity';
	
	
	public static function setUpBeforeClass(): void
	{
		$conn = getSanityConnector();
		
		$conn->db()->dropIfExists(self::MAIN_DB);
		$conn->db()->create(self::MAIN_DB);
	}
	
	public static function tearDownAfterClass(): void
	{
		$conn = getSanityConnector();
		$conn->db()->dropIfExists(self::MAIN_DB);
	}
	
	
	private function designCmd(?string $doc): CmdDesign
	{
		$cmd = getSanityConnector()
			->design()
			->db(self::MAIN_DB);
		
		if ($doc)
			$cmd->name($doc);
		
		return $cmd;
	}
	
	private function getCmd(?string $design = null, ?string $view = null): ICmdBulkGet
	{
		$cmd = getSanityConnector()
			->getAll()
			->from(self::MAIN_DB);
		
		if ($design)
			$cmd->view($design, $view);
		
		return $cmd;
	}
	
	private function store(array $data)
	{
		$i = 0;
		
		foreach ($data as &$item)
		{
			if (!isset($item['_id']))
				$item['_id'] = (string)($i++);
		}
		
		getSanityConnector()
			->storeAll()
			->into(self::MAIN_DB)
			->dataSet($data)
			->overrideConflict()
			->execute();
	}
	
	
	public function test_ViewIndexCreated()
	{
		$this->designCmd(__FUNCTION__)
			->addView('a', 'function(doc) {}')
			->create();
		
		$get = $this->getCmd(__FUNCTION__, 'a')->queryList();
		
		self::assertEquals(0, $get->count());
	}
	
	
	public function test_ViewIndexAlreadyExists_Ignore_OldViewPreserved()
	{
		$this->designCmd(__FUNCTION__)
			->addView('view_a', 'function(doc) { emit(doc.index_a); }')
			->create();
		
		$this->designCmd(__FUNCTION__)
			->addView('view_b', 'function(doc) { emit(doc.index_b); }')
			->ignoreConflict()
			->create();
		
		$this->store([
			[
				'index_a' => 'a',
				'index_b' => 'b'			
			]
		]);
		
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_a')->key('a')->queryValues());
		
		$this->getCmd(__FUNCTION__, 'view_b')->executeSafe($e);
		self::assertNotNull($e);
	}
	
	
	public function test_ViewIndexAlreadyExists_Override_NewViewCreated()
	{
		$this->designCmd(__FUNCTION__)
			->addView('view_a', 'function(doc) { emit(doc.index_a); }')
			->create();
		
		$this->designCmd(__FUNCTION__)
			->addView('view_b', 'function(doc) { emit(doc.index_b); }')
			->overrideConflict()
			->create();
		
		$this->store([
			[
				'index_a' => 'a',
				'index_b' => 'b'			
			]
		]);
		
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_b')->key('b')->queryValues());
		
		$this->getCmd(__FUNCTION__, 'view_a')->executeSafe($e);
		self::assertNotNull($e);
	}
	
	
	/**
	 * @expectedException \Snuggle\Exceptions\Http\ConflictException
	 */
	public function test_ViewIndexAlreadyExists_Throw_ExceptionThrown()
	{
		$this->designCmd(__FUNCTION__)
			->addView('view_a', 'function(doc) { emit(doc.index_a); }')
			->create();
		
		$this->designCmd(__FUNCTION__)
			->addView('view_b', 'function(doc) { emit(doc.index_b); }')
			->failOnConflict()
			->create();
	}
	
	
	public function test_ViewIndexAlreadyExists_AddNewDataOnly_NewViewAddedAndOldPreserved()
	{
		$this->designCmd(__FUNCTION__)
			->addView('view_a', 'function(doc) { emit(doc.index_a); }')
			->addView('view_b', 'function(doc) { emit(doc.index_b); }')
			->create();
		
		$this->designCmd(__FUNCTION__)
			->addView('view_b', 'function(doc) { emit(doc.index_b_wrong); }')
			->addView('view_c', 'function(doc) { emit(doc.index_c); }')
			->mergeNewOnConflict()
			->create();
		
		$this->store([
			[
				'index_a' => 'a',
				'index_b' => 'b_correct',
				'index_b_wrong' => 'b_wrong',
				'index_c' => 'c'			
			]
		]);
		
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_a')->key('a')->queryValues());
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_b')->key('b_correct')->queryValues());
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_c')->key('c')->queryValues());
	}
	
	
	public function test_ViewIndexAlreadyExists_KeepOldDataOnly_NewViewOverriddenButExistingPreserved()
	{
		$this->designCmd(__FUNCTION__)
			->addView('view_a', 'function(doc) { emit(doc.index_a); }')
			->addView('view_b', 'function(doc) { emit(doc.index_b_wrong); }')
			->create();
		
		$this->designCmd(__FUNCTION__)
			->addView('view_b', 'function(doc) { emit(doc.index_b); }')
			->addView('view_c', 'function(doc) { emit(doc.index_c); }')
			->mergeOverOnConflict()
			->create();
		
		$this->store([
			[
				'index_a' => 'a',
				'index_b' => 'b_correct',
				'index_b_wrong' => 'b_wrong',
				'index_c' => 'c'			
			]
		]);
		
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_a')->key('a')->queryValues());
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_b')->key('b_correct')->queryValues());
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'view_c')->key('c')->queryValues());
	}
	
	
	public function test_ReadIndexes_FromDir()
	{
		$this->designCmd(__FUNCTION__)
			->fromDir(__DIR__ . '/design/from_dir/rec', '*.js')
			->viewsFromDir(__DIR__ . '/design/from_dir/view_dir')
			->overrideConflict()
			->create();
		
		$this->store([
			[
				'index_b' => 'b',
				'index_wrong' => 'wrong',
				'index_c' => 'c',
				'index_d' => 'd',
				'index_e' => 'e'			
			],
			[
				'index_b' => 'b',
				'index_wrong' => 'wrong',
				'index_c' => 'c',
				'index_d' => 'd_2',
				'index_e' => 'e'			
			]
		]);
		
		self::assertCount(2, $this->getCmd(__FUNCTION__, 'b')->key('b')->queryValues());
		self::assertCount(1, $this->getCmd(__FUNCTION__, 'd')->key('d')->queryValues());
		self::assertCount(2, $this->getCmd(__FUNCTION__, 'e')->key('e')->queryValues());
		
		// Has '_sum' reducer
		self::assertEquals([2], $this->getCmd(__FUNCTION__, 'c')->key('c')->queryValues());
		
		$this->getCmd(__FUNCTION__, 'wrong')->executeSafe($e);
		self::assertInstanceOf(NotFoundException::class, $e);
		
	}
}