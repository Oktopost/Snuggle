<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;


interface ICmdGet extends IExecute, IQuery, IQueryRevision, IRevCommand, IReadOperation
{
	/**
	 * @param bool $ignoreMissing If true and document not found, null is returned instead of 404 exception.
	 * @return ICmdGet
	 */
	public function ignoreMissing(bool $ignoreMissing = true): ICmdGet;
	
	/**
	 * @param bool $include
	 * @param string|string[]|null $since
	 * @return ICmdGet
	 */
	public function withAttachments(bool $include = true, $since = null): ICmdGet;
	
	public function withAttachmentsEncoding(bool $include = true): ICmdGet;
	
	public function withConflicts(bool $include = true): ICmdGet;
	public function withDeleteConflicts(bool $include = true): ICmdGet;
	public function withRevisions(bool $include = true, bool $withInfo = true): ICmdGet;
	public function withRevisionsInfo(bool $withInfo = true): ICmdGet;
	public function withMeta(bool $include = true): ICmdGet;
	
	public function withLocalSeq(bool $include = true): ICmdGet;
	
	public function forceLatest(bool $force = true): ICmdGet;
	
	
	public function queryExists(?string $target = null, ?string $id = null): bool;
	
	/**
	 * @param string|null $target Document ID or Database name
	 * @param string|null $id If set, the documents ID. 
	 * @return Doc|null
	 */
	public function queryDoc(?string $target = null, ?string $id = null): ?Doc;
}