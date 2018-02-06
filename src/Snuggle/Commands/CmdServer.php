<?php
namespace Snuggle\Commands;


use Snuggle\Base\IConnection;
use Snuggle\Core\Server\Index;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Exceptions\SnuggleException;


class CmdServer implements ICmdServer
{
	/** @var IConnection */
	private $connection;
	
	
	private function __construct(IConnection $connection)
	{
		$this->connection = $connection;
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