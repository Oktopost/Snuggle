<?php
namespace Snuggle\Base;


use Snuggle\Base\Commands\ICmdDB;
use Snuggle\Base\Commands\ICmdGet;
use Snuggle\Base\Commands\ICmdStore;
use Snuggle\Base\Commands\ICmdDelete;
use Snuggle\Base\Commands\ICmdInsert;
use Snuggle\Base\Commands\ICmdServer;
use Snuggle\Base\Commands\ICmdDirect;


interface IConnector
{
	public function db(): ICmdDB; 
	public function get(): ICmdGet;
	public function store(): ICmdStore;
	public function insert(): ICmdInsert;
	public function delete(): ICmdDelete;
	public function server(): ICmdServer; 
	public function direct(): ICmdDirect;
}