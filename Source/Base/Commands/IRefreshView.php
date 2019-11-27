<?php
namespace Snuggle\Base\Commands;


interface IRefreshView
{
	/**
	 * @param int $operationsCount
	 * @return IRefreshView|static
	 */
	public function setRefreshOperations(int $operationsCount): IRefreshView;

	/**
	 * @param float $probability
	 * @return IRefreshView|static
	 */
	public function setRefreshProbability(float $probability): IRefreshView;

	/**
	 * @param string $designDocName
	 * @param string $refreshView
	 * @return IRefreshView|static
	 */
	public function setRefreshDoc(string $designDocName, ?string $refreshView = null): IRefreshView;

	/**
	 * @param array $designDocNames
	 * @param string $refreshView
	 * @return IRefreshView|static
	 */
	public function setRefreshDocs(array $designDocNames, ?string $refreshView = null): IRefreshView;
	
	/**
	 * @param array $designDocNamesToViews
	 * @return IRefreshView|static
	 */
	public function setRefreshDocViews(array $designDocNamesToViews): IRefreshView;
}