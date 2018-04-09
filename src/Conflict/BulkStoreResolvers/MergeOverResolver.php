<?php
namespace Snuggle\Conflict\BulkStoreResolvers;


use Snuggle\Core\Doc;
use Snuggle\Conflict\RecursiveMerge;


class MergeOverResolver extends DocStoreResolver
{
	protected function resolveDocs(Doc $new, Doc $current): ?Doc
	{
		$new->Data = RecursiveMerge::merge($current->Data, $new->Data);
		return $new;
	}
}