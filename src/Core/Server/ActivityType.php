<?php
namespace Snuggle\Core\Server;


use Traitor\TEnum;


class ActivityType
{
	use TEnum;
	
	
	public const DATABASE_COMPACTION	= 'database_compaction';
	public const INDEXER				= 'indexer';
	public const REPLICATION			= 'replication';
}