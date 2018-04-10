<?php
namespace Snuggle\Commands;


use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\ICmdDesign;
use Snuggle\Base\Connection\Response\IRawResponse;

use Snuggle\Commands\Abstraction\TQuery;
use Snuggle\Commands\Abstraction\TExecuteSafe;
use Snuggle\Commands\Abstraction\TQueryRevision;

use Snuggle\Design\DirectoryScanner;
use Snuggle\Conflict\RecursiveMerge;
use Snuggle\Exceptions\FatalSnuggleException;


class CmdDesign implements ICmdDesign
{
	use TQuery;
	use TExecuteSafe;
	use TQueryRevision;
	
	
	private $body = [
		'_id'		=> null,
		'language'	=> 'javascript'
	];
	
	/** @var ICmdStore */
	private $store;
	
	
	public function __construct(ICmdStore $store)
	{
		$this->store = $store;
	}
	
	public function __clone()
	{
		$this->store = clone $this->store;
	}
	
	
	public function document(string $db, string $name): ICmdDesign
	{
		return $this->db($db)->name($name);
	}
	
	public function db(string $name): ICmdDesign
	{
		$this->store->into($name);
		return $this;
	}
	
	public function name(string $name): ICmdDesign
	{
		$this->body['_id'] = "_design/$name";
		return $this;
	}
	
	public function language(string $lang): ICmdDesign
	{
		$this->body['language'] = $lang;
		return $this;
	}
	
	public function data(array $data): ICmdDesign
	{
		$this->body = RecursiveMerge::merge($this->body, $data);
		return $this;
	}
	
	public function addView(string $name, string $map, ?string $reduce = null): ICmdDesign
	{
		$this->body['views'][$name] = ['map' => $map];
		
		if ($reduce)
			$this->body['views'][$name]['reduce'] = $reduce;
		
		return $this;
	}
	
	public function rev(?string $rev): ICmdDesign
	{
		if ($rev)
			$this->body['_rev'] = $rev;
		else
			unset($this->body['_rev']);
		
		return $this;
	}
	
	public function addViews(array $views): ICmdDesign
	{
		$this->body['views'] = array_merge(
			$this->body['views'] ?? [],
			$views
		);
		
		return $this;
	}
	
	
	public function ignoreConflict(): ICmdDesign
	{
		$this->store->ignoreConflict();
		return $this;
	}
	
	public function overrideConflict(): ICmdDesign
	{
		$this->store->overrideConflict();
		return $this;
	}
	
	public function failOnConflict(): ICmdDesign
	{
		$this->store->failOnConflict();
		return $this;
	}
	
	public function mergeNewOnConflict(): ICmdDesign
	{
		$this->store->mergeNewOnConflict();
		return $this;
	}
	
	public function mergeOverOnConflict(): ICmdDesign
	{
		$this->store->mergeOverOnConflict();
		return $this;
	}
	
	public function fromDir(string $path, string $fileFilter = '*'): ICmdDesign
	{
		return $this->data(DirectoryScanner::scanDir($path, $fileFilter));
	}
	
	public function viewsFromDir(string $path, string $fileFilter = '*'): ICmdDesign
	{
		return $this->data(DirectoryScanner::scanViewsDir($path, $fileFilter));
	}
	
	public function create(): void
	{
		$this->execute();
	}
	
	public function execute(): IRawResponse
	{
		if (!$this->body['_id'])
			throw new FatalSnuggleException('Database name and Design name must be set!');
		
		return $this->store
			->data($this->body)
			->execute();
	}
}