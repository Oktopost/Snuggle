<?php
namespace Snuggle\Connection\Parsers\DB;


use Traitor\TStaticClass;

use Snuggle\Core\DB\DDocInfo;
use Snuggle\Base\Connection\Response\IRawResponse;


class DDocInfoParser
{
	use TStaticClass;
	
	
	private static function getViewSizes(DDocInfo $info, array $data): void
	{
		// data_size is for CouchDB 2.*
		// size[...] is for CouchDB 3.*
		
 		$info->DataSize = (int)($data['data_size'] ?? $data['sizes']['external'] ?? 0);
 		$info->DiskSize	= (int)($data['disk_size'] ?? $data['sizes']['file'] ?? 0);
	}
	
	
	public static function parse(IRawResponse $response): DDocInfo
	{
		$body = $response->getJsonBody();
		$indexInfo = $body['view_index'] ?? [];
		
		if (!is_array($indexInfo))
			$indexInfo = [];
		
		$data = new DDocInfo();
		$data->setSource(is_array($body) ? $body : []);
		
		self::getViewSizes($data, $indexInfo);
		
		$data->Name				= $body['name'] ?? '';
		$data->Signature		= $indexInfo['signature'] ?? null;
 		$data->Language			= $indexInfo['language'] ?? 'unknown';
 		$data->PurgeSeq			= (int)($indexInfo['purge_seq'] ?? 0);
 		$data->UpdateSeq		= $indexInfo['update_seq'] ?? 0;
 		$data->IsUpdaterRunning	= (bool)($indexInfo['updater_running'] ?? false);	
 		$data->WaitingClients	= (int)($indexInfo['waiting_clients'] ?? 0);	
 		$data->IsWaitingCommit	= (bool)($indexInfo['waiting_commit'] ?? false);	
		
		return $data;
	}
}