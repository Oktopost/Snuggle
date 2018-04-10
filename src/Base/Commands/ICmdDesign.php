<?php
namespace Snuggle\Base\Commands;


interface ICmdDesign extends IExecute, IQueryRevision, IQuery
{
	public function document(string $db, string $name): ICmdDesign;
	public function db(string $name): ICmdDesign;
	public function name(string $name): ICmdDesign;
	public function rev(?string $rev): ICmdDesign;
	public function language(string $lang): ICmdDesign;
	public function data(array $data): ICmdDesign;
	public function addView(string $name, string $map, ?string $reduce = null): ICmdDesign;
	public function addViews(array $views): ICmdDesign;
	
	public function ignoreConflict(): ICmdDesign;
	public function overrideConflict(): ICmdDesign;
	public function failOnConflict(): ICmdDesign;
	public function mergeNewOnConflict(): ICmdDesign;
	public function mergeOverOnConflict(): ICmdDesign;
	
	public function fromDir(string $path, string $fileFilter = '*'): ICmdDesign;
	public function viewsFromDir(string $path, string $fileFilter = '*'): ICmdDesign;
	
	public function create(): void;
}