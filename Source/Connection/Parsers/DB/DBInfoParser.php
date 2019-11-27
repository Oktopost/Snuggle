<?php
namespace Snuggle\Connection\Parsers\DB;


use Traitor\TStaticClass;

use Snuggle\Core\DB\DBInfo;
use Snuggle\Base\Connection\Response\IRawResponse;


class DBInfoParser
{
	use TStaticClass;
	
	
	public static function parse(IRawResponse $response): DBInfo
	{
		$body = $response->getJsonBody();
		
		$data = new DBInfo();
		$data->setSource(is_array($body) ? $body : []);
		
		$data->Name					= $body['db_name'] ?? '';
		$data->UUID					= $body['uuid'] ?? '';
		$data->CompactRunning		= $body['compact_running'] ?? false;
		$data->DiskFormatVersion	= $body['disk_format_version'] ?? -1;
		$data->DocsCount			= $body['doc_count'] ?? 0;
		$data->DeletedDocsCount		= $body['doc_del_count'] ?? 0;
		$data->UpdateSeq			= $body['update_seq'] ?? '';
		$data->PurgeSeq				= $body['update_seq'] ?? 0;
		
		$data->Sizes->File		= $body['sizes']['file'] ?? 0;
		$data->Sizes->Active	= $body['sizes']['active'] ?? 0;
		$data->Sizes->External	= $body['sizes']['external'] ?? 0;
		
		if ($body['cluster'] ?? false)
		{
			$data->Cluster->IsClustered = true;
			
			$data->Cluster->Replicas	= $body['cluster']['n'] ?? 0;
			$data->Cluster->Shards		= $body['cluster']['q'] ?? 0;
			$data->Cluster->ReadQuorum	= $body['cluster']['r'] ?? 0;
			$data->Cluster->WriteQuorum	= $body['cluster']['w'] ?? 0;
		}
		
		return $data;
	}
}