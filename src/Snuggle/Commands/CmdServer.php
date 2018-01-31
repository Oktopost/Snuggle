<?php
namespace Snuggle\Commands;


use Snuggle\Core\Server\Index;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Exceptions\SnuggleException;


class CmdServer extends AbstractCommand implements ICmdServer
{
	public function info(): Index
	{
		$result = $this->requestURI('/');
		
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