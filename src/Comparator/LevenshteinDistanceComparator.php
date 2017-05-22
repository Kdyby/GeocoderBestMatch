<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\BestMatch\Comparator;

use Geocoder\Model\Address;
use Kdyby\Geocoder\BestMatch\AddressComparator;

class LevenshteinDistanceComparator implements \Kdyby\Geocoder\BestMatch\AddressComparator
{

	use \Kdyby\StrictObjects\Scream;

	/**
	 * @var \Kdyby\Geocoder\BestMatch\AddressComparator|NULL
	 */
	private $fallback;

	public function __construct(AddressComparator $fallback = NULL)
	{
		$this->fallback = $fallback;
	}

	/**
	 * {@inheritDoc}
	 */
	public function compare(Address $a, Address $b, $geocoderQuery)
	{
		$aL = (int) levenshtein($this->formatSimpleFull($a), $geocoderQuery);
		$bL = (int) levenshtein($this->formatSimpleFull($b), $geocoderQuery);

		if ($aL !== $bL) {
			return $aL > $bL ? 1 : -1;
		}

		return $this->fallback !== NULL ? $this->fallback->compare($a, $b, $geocoderQuery) : 0;
	}

	private function formatSimpleFull(Address $address)
	{
		return ($address->getStreetName() ? $this->formatStreet($address) . ', ' : '') . $address->getLocality();
	}

	private function formatStreet(Address $address)
	{
		return $address->getStreetName() . ($address->getStreetNumber() ? ' ' . $address->getStreetNumber() : '');
	}

}
