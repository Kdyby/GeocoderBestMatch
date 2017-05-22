<?php

/**
 * Test: Kdyby\Geocoder\BestMatch\Comparator\LevenshteinDistanceComparator.
 *
 * @testCase
 */

namespace KdybyTests\Geocoder\BestMatch;

use Geocoder\Model\Address;
use Kdyby\Geocoder\BestMatch\Comparator\LevenshteinDistanceComparator;
use Kdyby\Geocoder\BestMatch\StableSort;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class LevenshteinDistanceComparatorTest extends \Tester\TestCase
{

	/**
	 * @dataProvider dataCompare
	 */
	public function testCompare($expected, $query, Address $a, Address $b)
	{
		$comparator = new LevenshteinDistanceComparator();
		Assert::same($expected, $comparator->compare($a, $b, $query));
	}

	public function dataCompare()
	{
		return [
			[
				1,
				'Soukenická 5',
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			],
			[
				-1,
				'Soukenická 5',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
			],
			[
				0,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			],
			[
				1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			],
			[
				-1,
				'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
			],
			[
				1,
				'Soukenická 5',
				Helpers::createAddress('Hradec Králové', 'Soukenická', NULL, 5),
				Helpers::createAddress('Plzeň', 'Soukenická', NULL, 5),
			],
			[
				1,
				'Soukenická 5',
				Helpers::createAddress('Hradec Kralove', 'Soukenická', NULL, 5),
				Helpers::createAddress('Plzeň', 'Soukenická', NULL, 5),
			],
		];
	}

	public function testFunctional()
	{
		$query = 'Soukenická 5';
		$list = [
			$b1 = Helpers::createAddress('Brno', 'Soukenicka', NULL, 5),
			$b2 = Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			$p = Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
			$g = Helpers::createAddress('Olomouc', 'Soukenická', NULL, 5),
			$e = Helpers::createAddress('Liberec', 'Soukenická', NULL, 5),
			$c = Helpers::createAddress('Ostrava', 'Soukenická', NULL, 5),
		];

		$comparator = new LevenshteinDistanceComparator();
		$list = StableSort::sort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b2, $p, $b1, $g, $e, $c], $list);
	}

}

(new LevenshteinDistanceComparatorTest())->run();
