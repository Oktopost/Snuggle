<?php
namespace Snuggle\Connection\Parsers\DB;


use Traitor\TStaticClass;

use Snuggle\Core\DB\DDocInfo;
use Snuggle\Base\Connection\Response\IRawResponse;


class DDocInfoParser
{
	use TStaticClass;
	
	
	public static function parse(IRawResponse $response): DDocInfo
	{
		$body = $response->getJsonBody();
		$indexInfo = $body['view_index'] ?? [];
		
		if (!is_array($indexInfo))
			$indexInfo = [];
		
		$data = new DDocInfo();
		$data->setSource(is_array($body) ? $body : []);
		
		$data->Name				= $body['name'] ?? '';
		$data->Signature		= $indexInfo['signature'] ?? null;
 		$data->DataSize			= (int)($indexInfo['data_size'] ?? 0);
 		$data->DiskSize			= (int)($indexInfo['disk_size'] ?? 0);
 		$data->Language			= $indexInfo['language'] ?? 'unknown';
 		$data->PurgeSeq			= (int)($indexInfo['purge_seq'] ?? 0);
 		$data->UpdateSeq		= $indexInfo['update_seq'] ?? 0;
 		$data->IsUpdaterRunning	= (bool)($indexInfo['updater_running'] ?? false);	
 		$data->WaitingClients	= (int)($indexInfo['waiting_clients'] ?? 0);	
 		$data->IsWaitingCommit	= (bool)($indexInfo['waiting_commit'] ?? false);	
		
		return $data;
	}
}