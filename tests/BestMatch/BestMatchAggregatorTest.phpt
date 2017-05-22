<?php

/**
 * Test: Kdyby\Geocoder\BestMatch\BestMatchAggregator.
 *
 * @testCase
 */

namespace KdybyTests\Geocoder\BestMatch;

use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\ProviderAggregator;
use Kdyby\Geocoder\BestMatch\AddressComparator;
use Kdyby\Geocoder\BestMatch\BestMatchAggregator;
use Mockery;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class BestMatchAggregatorTest extends \Tester\TestCase
{

	public function testGeocoder()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5, 559);
		$b = Helpers::createAddress('Brno', 'Soukenická', 5);

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider1 */
		$provider1 = Mockery::mock(Provider::class);
		$provider1->shouldReceive('geocode')->andReturn(new AddressCollection([$a]));

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider1 */
		$provider2 = Mockery::mock(Provider::class);
		$provider2->shouldReceive('geocode')->andReturn(new AddressCollection([$b]));

		/** @var \Geocoder\ProviderAggregator|\Mockery\MockInterface $aggregator */
		$aggregator = Mockery::mock(ProviderAggregator::Class);
		$aggregator->shouldReceive('getLimit')->andReturn(5);
		$aggregator->shouldReceive('getProviders')->andReturn([
			$provider1,
			$provider2,
		]);

		/** @var \Kdyby\Geocoder\BestMatch\AddressComparator|\Mockery\MockInterface $comparator */
		$comparator = Mockery::mock(AddressComparator::class);
		$comparator->shouldReceive('compare')->andReturnUsing(function ($j, $k) use ($a, $b) {
			return $j === $a ? -1 : 1;
		});

		$geocoder = new BestMatchAggregator($aggregator, $comparator);
		$result = $geocoder->geocode('Soukenická 5, Brno');

		Assert::same($a, $result->first());
		Assert::same($a, $result->get(0));
		Assert::same($b, $result->get(1));
	}

	protected function tearDown()
	{
		Mockery::close();
	}

}

(new BestMatchAggregatorTest())->run();
