<?php
namespace Snuggle\Scripts\Compact;


use PHPUnit\Framework\TestCase;


class DBNameFilterTest extends TestCase
{
	public function test_EmptyDBArray_EmptyArrayReturned(): void
	{
		self::assertSame([], DBNameFilter::filter([], ['*']));
	}
	
	public function test_EmptyFilter_AllDBsReturned(): void
	{
		self::assertSame(['a', 'b'], DBNameFilter::filter(['a', 'b'], []));
	}
	
	public function test_MatchFilter_DBsMatchingFilterOnlyReturned(): void
	{
		self::assertSame(['a', 'ab'], DBNameFilter::filter(['a', 'b', 'ab'], ['a*']));
	}
	
	public function test_NegateFilter_OnlyDBsNotMatchingTheFilterReturned(): void
	{
		self::assertSame(['b'], DBNameFilter::filter(['a', 'b', 'ab'], ['a*', 'b*', '!a*']));
	}
	
	public function test_NegateFilter_HaveOnlyNegateMatch_OtherNamesReturned(): void
	{
		self::assertSame(['b'], DBNameFilter::filter(['a', 'b'], ['!a']));
	}
	
	public function test_DifferentFilters_AnyDBMatchingAtLeastOneFilterReturned(): void
	{
		self::assertSame(['aa', 'bb'], DBNameFilter::filter(['aa', 'bb', 'ca'], ['a*', 'b*']));
	}
	
	public function test_DifferentFiltersWithNegate_AnyDBMatchingAtLeastOneFilterButNoneOfTheNegateReturned(): void
	{
		self::assertSame(['acc', 'cca'], DBNameFilter::filter(['abc', 'acc', 'cbb', 'cca'], ['*a*', '!*b*', '*c*']));
	}
}