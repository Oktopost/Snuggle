<?php
namespace Snuggle\Commands;


use PHPUnit\Framework\TestCase;
use Snuggle\Base\IConnection;
use Snuggle\Base\Connection\Request\IRawRequest;
use Snuggle\Connection\Method;
use Snuggle\Connection\Response\RawResponse;
use Snuggle\Exceptions\FatalSnuggleException;


/**
 * @group unit
 */
class CmdChangesTest extends TestCase
{
	public function test_execute_ViewFilterConfigured_GetRequestCreated(): void
	{
		$connection = $this->createMock(IConnection::class);
		$connection
			->expects(self::once())
			->method('request')
			->willReturnCallback(function (IRawRequest $request)
			{
				self::assertSame('emails/_changes', $request->getURI());
				self::assertSame(Method::GET, $request->getMethod());
				self::assertSame([
					'descending'  => 'true',
					'limit'       => '10',
					'since'       => '"5-sequence"',
					'style'       => '"all_docs"',
					'include_docs'=> 'true',
					'filter'      => '_view',
					'view'        => 'mail/by_status'
				], $request->getQueryParams());
				self::assertFalse($request->hasBody());

				return new RawResponse($request, 200, [], '{"results":[],"last_seq":"5-sequence","pending":0}');
			});

		$list = (new CmdChanges($connection))
			->db('emails')
			->descending()
			->limit(10)
			->since('5-sequence')
			->style('all_docs')
			->includeDocs()
			->view('mail', 'by_status')
			->queryList();

		self::assertSame('5-sequence', $list->LastSeq);
		self::assertSame(0, $list->Pending);
		self::assertSame(0, $list->count());
	}

	public function test_queryDocs_SelectorConfigured_PostRequestCreatedAndDocsReturned(): void
	{
		$selector = ['Status' => 'ready'];
		$connection = $this->createMock(IConnection::class);
		$connection
			->expects(self::once())
			->method('request')
			->willReturnCallback(function (IRawRequest $request) use ($selector)
			{
				self::assertSame(Method::POST, $request->getMethod());
				self::assertSame([
					'filter'       => '_selector',
					'include_docs' => 'true'
				], $request->getQueryParams());
				self::assertSame(['selector' => $selector], jsondecode($request->getBody(), true));

				return new RawResponse($request, 200, [], jsonencode([
					'results' => [
						['id' => 'doc-1', 'doc' => ['_id' => 'doc-1', 'Status' => 'ready']],
						['id' => 'doc-2', 'error' => 'forbidden'],
						['id' => 'doc-3']
					]
				]));
			});

		$docs = (new CmdChanges($connection))
			->db('entities')
			->selector($selector)
			->queryDocs();

		self::assertCount(1, $docs);
		self::assertSame('doc-1', $docs[0]->ID);
		self::assertSame('ready', $docs[0]->Data['Status']);
	}

	public function test_execute_DatabaseNotConfigured_ExceptionThrown(): void
	{
		$this->expectException(FatalSnuggleException::class);

		$connection = $this->createMock(IConnection::class);
		(new CmdChanges($connection))->execute();
	}
}
