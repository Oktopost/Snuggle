<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;
use Snuggle\Conflict\RecursiveMerge;


class MergeNewResolver extends DocStoreResolver
{
	protected function resolveDocs(Doc $new, Doc $current): ?Doc
	{
		$new->Data = RecursiveMerge::merge($new->Data, $current->Data);
		return $new;
	}
}