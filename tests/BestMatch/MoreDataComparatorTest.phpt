<?php

/**
 * Test: Kdyby\Geocoder\BestMatch\Comparator\MoreDataComparator.
 *
 * @testCase
 */

namespace KdybyTests\Geocoder\BestMatch;

use Geocoder\Model\Address;
use Kdyby\Geocoder\BestMatch\Comparator\MoreDataComparator;
use Kdyby\Geocoder\BestMatch\StableSort;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class MoreDataComparatorTest extends \Tester\TestCase
{

	/**
	 * @dataProvider dataCompare
	 */
	public function testCompare($expected, $query, Address $a, Address $b)
	{
		$comparator = new MoreDataComparator();
		Assert::same($expected, $comparator->compare($a, $b, $query));
	}

	public function dataCompare()
	{
		return [
			[
				1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Hlavní', 5),
				Helpers::createAddress('Brno', 'Soukenická', 5, 559),
			],
			[
				1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Praha', 'Soukenická', 5),
				Helpers::createAddress('Brno', 'Soukenická', 5, 559),
			],
			[
				0,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 5),
				Helpers::createAddress('Brno', 'Soukenická', 5),
			],
			[
				0,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			],
			[
				-1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
				Helpers::createAddress('Brno', 'Soukenická', 5),
			],
			[
				1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 5),
				Helpers::createAddress('Brno', 'Soukenická', 5, 559), // wrong order
			],
			[
				-1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
				Helpers::createAddress('Brno', 'Soukenická'),
			],
			[
				1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická'),
				Helpers::createAddress('Brno', 'Soukenická', 5, 559), // wrong order
			],
			[
				0,
				'Soukenická 559, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559),
				Helpers::createAddress('Brno', 'Soukenická', 559),
			],
			[
				0,
				'Soukenická 559, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			],
			[
				-1,
				'Soukenická 559, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			],
			[
				-1,
				'Soukenická 559, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 559),
				Helpers::createAddress('Brno', 'Soukenická', 5),
			],
			[
				0,
				'Soukenická 559/5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			],
			[
				0,
				'Soukenická 5/559, Brno', // wrong order
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
				Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			],
			[
				1,
				'Dobrovodská 2767/23a, České budějovice',
				Helpers::createAddress('České budějovice', 'Dobrovodská', '38', '23'),
				Helpers::createAddress('České budějovice', 'Dobrovodská', '2767', '23a'),
			],
		];
	}

	public function testSortSameCityOverNumbers()
	{
		$query = 'Pobřežní 46, Kolín';
		$list = [
			$a = Helpers::createAddress('Kutná Hora', 'Pobřežní', 3, NULL, '284 01'),
			$b = Helpers::createAddress('Kolín', 'Pobřežní', NULL, NULL, '280 02'),
			$c = Helpers::createAddress('Týnec nad Labem', 'Pobřežní', NULL, NULL, '281 26'),
			$d = Helpers::createAddress('Veltruby', 'Pobřežní', NULL, NULL, '280 02'),
		];

		$comparator = new MoreDataComparator();
		$list = StableSort::sort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b, $a, $c, $d], $list);
	}

	public function testSortFullNumberInInput()
	{
		$query = 'Soukenická 559/5, Brno';
		$list = [
			$a = Helpers::createAddress('Brno', 'Soukenická'),
			$b = Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			$c = Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			$d = Helpers::createAddress('Brno', 'Soukenická', 559),
			$e = Helpers::createAddress('Brno', 'Hlavní', 5),
			$f = Helpers::createAddress('Praha', 'Soukenická', 5),
		];

		$comparator = new MoreDataComparator();
		$list = StableSort::sort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b, $c, $d, $a, $e, $f], $list);
	}

	public function testSortHouseNumberInInput()
	{
		$query = 'Soukenická 559, Brno';
		$list = [
			$a = Helpers::createAddress('Brno', 'Soukenická'),
			$b = Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			$c = Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			$d = Helpers::createAddress('Brno', 'Soukenická', 559),
			$e = Helpers::createAddress('Brno', 'Hlavní', 5),
			$f = Helpers::createAddress('Praha', 'Soukenická', 5),
		];

		$comparator = new MoreDataComparator();
		$list = StableSort::sort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b, $d, $a, $c, $e, $f], $list);
	}

	public function testSortOrientationNumberInInput()
	{
		$query = 'Soukenická 5, Brno';
		$list = [
			$a = Helpers::createAddress('Brno', 'Soukenická'),
			$b = Helpers::createAddress('Brno', 'Soukenická', 559, 5),
			$c = Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			$d = Helpers::createAddress('Brno', 'Soukenická', 559),
			$e = Helpers::createAddress('Brno', 'Hlavní', 5),
			$f = Helpers::createAddress('Praha', 'Soukenická', 5),
		];

		$comparator = new MoreDataComparator();
		$list = StableSort::sort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b, $c, $a, $d, $e, $f], $list);
	}

}

(new MoreDataComparatorTest())->run();
