<?php

/**
 * Test: Kdyby\Geocoder\BestMatch\BestMatchProvider.
 *
 * @testCase
 */

namespace KdybyTests\Geocoder\BestMatch;

use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Kdyby\Geocoder\BestMatch\AddressComparator;
use Kdyby\Geocoder\BestMatch\BestMatchProvider;
use Mockery;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class BestMatchProviderTest extends \Tester\TestCase
{

	public function testGeocoder()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5, 559);
		$b = Helpers::createAddress('Brno', 'Soukenická', 5);

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider */
		$provider = Mockery::mock(Provider::class);
		$provider->shouldReceive('geocode')->andReturn(new AddressCollection([$a, $b]));
		$provider->shouldReceive('limit')->andReturn($provider);

		/** @var \Kdyby\Geocoder\BestMatch\AddressComparator|\Mockery\MockInterface $comparator */
		$comparator = Mockery::mock(AddressComparator::class);
		$comparator->shouldReceive('compare')->andReturnUsing(function ($j, $k) use ($a, $b) {
			return $j === $a ? -1 : 1;
		});

		$geocoder = new BestMatchProvider($provider, $comparator);
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

(new BestMatchProviderTest())->run();
