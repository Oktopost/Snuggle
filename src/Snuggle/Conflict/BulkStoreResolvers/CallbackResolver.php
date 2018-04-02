<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;


class CallbackResolver extends DocStoreResolver
{
	private $callback;
	
	
	public function __construct($callback)
	{
		$this->callback = $callback;
	}
	
	
	protected function resolveDocs(Doc $new, Doc $current): ?Doc
	{
		$callback = $this->callback;
		return $callback($current, $new);
	}
}