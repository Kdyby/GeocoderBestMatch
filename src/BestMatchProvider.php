<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\BestMatch;

use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;

class BestMatchProvider extends \Geocoder\Provider\AbstractProvider implements \Geocoder\Provider\Provider
{

	use \Kdyby\StrictObjects\Scream;

	/**
	 * @var \Geocoder\Provider\Provider
	 */
	private $provider;

	/**
	 * @var \Kdyby\Geocoder\BestMatch\AddressComparator
	 */
	private $comparator;

	public function __construct(Provider $provider, AddressComparator $comparator)
	{
		parent::__construct();
		$this->provider = $provider;
		$this->comparator = $comparator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function geocode($value)
	{
		$allAddresses = $this->provider
			->limit($this->getLimit())
			->geocode($value)
			->all();

		StableSort::sort($allAddresses, function (Address $a, Address $b) use ($value) {
			return $this->comparator->compare($a, $b, $value);
		}); // intentionally, usorts cries when compared objects are modified

		return new AddressCollection($allAddresses);
	}

	/**
	 * {@inheritDoc}
	 */
	public function reverse($latitude, $longitude)
	{
		return $this->provider
			->limit($this->getLimit())
			->reverse($latitude, $longitude);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->provider->getName() . '_best_match';
	}

}
