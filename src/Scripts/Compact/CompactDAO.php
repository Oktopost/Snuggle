<?php
namespace Snuggle\Scripts\Compact;


use Snuggle\Base\IConnector;

use Objection\Mapper;
use Cartograph\Utilities\Narrow;


class CompactDAO
{
	private $db;
	
	/** @var IConnector */
	private $conn;
	
	
	public function __construct(IConnector $conn, string $db)
	{
		$this->db = $db;
		$this->conn = $conn;
	}
	
	
	public function getDataObject(string $date = null): ?InstanceData
	{
		$date = $date ?: date('Y-m-d');
		
		$data = $this->conn
			->get()
			->from($this->db)
			->ignoreMissing()
			->queryDoc("Compactor-$date");
		
		if (!$data)
			return null;
		
		/** @var InstanceData $object */
		$object = Mapper::getObjectFrom(InstanceData::class, $data->Data['Data'] ?? []);
		
		return $object;
	}
	
	public function saveDataObject(InstanceData $object): void
	{
		$record = [
			'_id'		=> "Compactor-{$object->Date}",
			'Data' 		=> Narrow::toArray($object)
		];
		
		$object->Date		= $object->Date ?: date('Y-m-d');
		$object->Created	= $object->Created ?: date('Y-m-d H:i:s');
		
		$this->conn
			->store()
			->into($this->db)
			->overrideConflict()
			->data($record)
			->execute();
	}
}