<?php
namespace Snuggle\Base\Conflict;


use Snuggle\Base\Connection\Response\IRawResponse;
use Snuggle\Exceptions\Http\ConflictException;


interface IConflictResolutionTemplate
{
	public function resolve(IRawResponse $response, ConflictException $e): IRawResponse;
	public function override(IRawResponse $response, ConflictException $e): IRawResponse;
	public function mergeNew(IRawResponse $response, ConflictException $e): IRawResponse;
	public function mergeOver(IRawResponse $response, ConflictException $e): IRawResponse;
}