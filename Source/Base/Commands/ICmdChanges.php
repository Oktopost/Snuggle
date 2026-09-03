<?php
namespace Snuggle\Base\Commands;


use Snuggle\Core\Doc;
use Snuggle\Core\Lists\ChangeRow;
use Snuggle\Core\Lists\ChangeList;


interface ICmdChanges extends IExecute, IQuery
{
	public function db(string $name): self;
	public function descending(bool $isDesc = true): self;
	public function limit(int $limit): self;
	public function since(string $id): self;
	public function style(string $style): self;
	public function view(?string $design, ?string $view = null): self;
	public function selector(array $selector): self;
	public function includeDocs(bool $include = true): self;

	public function queryList(): ChangeList;

	/**
	 * @return ChangeRow[]
	 */
	public function queryRows(): array;

	/**
	 * @return Doc[]
	 */
	public function queryDocs(): array;
}
