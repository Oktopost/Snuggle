<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Base\Commands\ICmdServer;

use Snuggle\Core\Server\Index;

use Snuggle\Exceptions\SnuggleException;


class CmdServer implements ICmdServer
{
	/** @var IConnection */
	private $connection;
	
	
	public function __construct(IConnection $connection)
	{
		$this->connection = $connection;
	}
	
	
	public function databases(): array
	{
		return $this->connection->request('/_all_dbs')->getJsonBody();
	}
	
	public function info(): Index
	{
		$result = $this->connection->request('/');
		
		if ($result->isFailed())
			throw new SnuggleException('Query Failed');
		
		$result = $result->getBody()->getJson(true);
		$info = new Index();
		
		$info->UUID				= $result['uuid'] ?? '';
		$info->Version			= $result['version'] ?? '';
		$info->Vendor->Name		= $result['vendor']['name'] ?? '';
		$info->Vendor->Version	= $result['vendor']['version'] ?? '';
		
		return $info;
	}
}