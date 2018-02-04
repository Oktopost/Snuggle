<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ISingleDoc;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Core\Doc;
use Snuggle\Commands\Common\TQuery;
use Snuggle\Commands\Abstraction\AbstractExecutable;
use Snuggle\Connection\Method;
use Snuggle\Exceptions\FatalSnuggleException;
use Snuggle\Exceptions\Http\NotFoundException;
use Snuggle\Connection\Parsers\SingleDocParser;

use Structura\Arrays;


class CmdGet extends AbstractExecutable implements ICmdGet
{
	use TQuery;
	
	
	private $db;
	private $id;
	
	private $params = [];
	private $ignoreMissing = false;
	
	
	private function validate(): void
	{
		if ($this->db && $this->id)
			return;
		
		throw new FatalSnuggleException('DB name AND document id must be set');
	}
	
	private function uri(): string
	{
		return $this->db . '/' . $this->id;
	}
	
	private function setBoolParam(string $name, bool $val): ICmdGet
	{
		if ($val)
			$this->params[$name] = 'true';
		else
			unset($this->params[$name]);
		
		return $this;
	}
	
	private function queryDocumentSafe(): ?Doc
	{
		try
		{
			return $this->queryDocumentUnsafe();
		}
		catch (NotFoundException $e)
		{
			return null;
		}
	}
	
	private function queryDocumentUnsafe(): Doc
	{
		return SingleDocParser::parse($this->execute());
	}
	
	
	/**
	 * @param string $db
	 * @return ISingleDoc|static
	 */
	public function from(string $db): ISingleDoc
	{
		$this->db = $db;
		return $this;
	}
	
	/**
	 * @param string $rev
	 * @return ISingleDoc|static
	 */
	public function rev(string $rev): ISingleDoc
	{
		$this->params['rev'] = $rev;
		return $this;
	}
	
	/**
	 * @param bool $ignoreMissing If true and document not found, null is returned instead of 404 exception.
	 * @return ICmdGet
	 */
	public function ignoreMissing(bool $ignoreMissing = true): ICmdGet
	{
		$this->ignoreMissing = $ignoreMissing;
		return $this;
	}
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID.
	 * @return ISingleDoc|static
	 */
	public function doc(string $target, ?string $id = null): ISingleDoc
	{
		if ($id)
		{
			$this->db = $target;
			$this->id = $id;
		}
		else
		{
			$this->id = $target;
		}
		
		return $this;
	}
	
	/**
	 * @param bool $include
	 * @param string|string[]|null $since
	 * @return ICmdGet
	 */
	public function withAttachments(bool $include = true, $since = null): ICmdGet
	{
		if (!$include)
		{
			unset($this->params['attachments']);
			unset($this->params['atts_since']);
		}
		else
		{
			$this->params['attachments'] = 'true';
			
			if ($since)
			{
				$this->params['atts_since'] = json_encode(Arrays::toArray($since));
			}
		}
		
		return $this;
	}
	
	public function withAttachmentsEncoding(bool $include = true): ICmdGet
	{
		return $this->setBoolParam('att_encoding_info', $include);
	}
	
	public function withConflicts(bool $include = true): ICmdGet
	{
		return $this->setBoolParam('conflicts', $include);
	}
	
	public function withDeleteConflicts(bool $include = true): ICmdGet
	{
		return $this->setBoolParam('deleted_conflicts', $include);
	}
	
	public function withRevisions(bool $include = true, bool $withInfo = true): ICmdGet
	{
		$this->setBoolParam('revs', $include);
		return $this->setBoolParam('revs_info', $withInfo);
	}
	
	public function withRevisionsInfo(bool $withInfo = true): ICmdGet
	{
		return $this->setBoolParam('revs_info', $withInfo);
	}
	
	public function withMeta(bool $include = true): ICmdGet
	{
		return $this->setBoolParam('meta', $include);
	}
	
	public function withLocalSeq(bool $include = true): ICmdGet
	{
		return $this->setBoolParam('local_seq', $include);
	}
	
	public function forceLatest(bool $force = true): ICmdGet
	{
		return $this->setBoolParam('latest', $force);
	}
	
	
	public function queryExists(): bool
	{
		$this->validate();
		
		try
		{
			$this->executeRequest(
				$this->uri(),
				Method::HEAD,
				$this->params);
		}
		catch (NotFoundException $e)
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param string $target Document ID or Database name
	 * @param string|null $id If set, the documents ID.
	 * @return Doc|null
	 */
	public function queryDoc(?string $target = null, ?string $id = null): ?Doc
	{
		if ($target)
			$this->doc($target, $id);
		
		return ($this->ignoreMissing ?
			$this->queryDocumentSafe() : 
			$this->queryDocumentUnsafe());
	}
	
	public function execute(): IRawResponse
	{
		return $this->executeRequest($this->uri(), Method::GET, $this->params);
	}
}