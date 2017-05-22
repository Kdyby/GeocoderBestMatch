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
use Kdyby\Geocoder\BestMatch\UserInputHelpers;

class MoreDataComparator implements \Kdyby\Geocoder\BestMatch\AddressComparator
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
	public function compare(Address $a, Address $b, $query)
	{
		$compareCity = $this->compareCity($a, $b, $query);
		if ($compareCity !== 0) {
			return $compareCity;
		}

		$compareStreets = $this->compareStreet($a, $b, $query);
		if ($compareStreets !== 0) {
			return $compareStreets;
		}

		$compareNumbers = $this->compareNumbers($a, $b, $query);
		if ($compareNumbers !== 0) {
			return $compareNumbers;
		}

		return $this->fallback !== NULL ? $this->fallback->compare($a, $b, $query) : 0;
	}

	protected function compareCity(Address $a, Address $b, $query)
	{
		if (!$a->getLocality() && !$b->getLocality()) {
			return 0;

		} elseif ($a->getLocality() && !$b->getLocality()) {
			return -1;

		} elseif ($b->getLocality() && !$a->getLocality()) {
			return 1;
		}

		if (stripos($query, $a->getLocality()) !== FALSE && stripos($query, $b->getLocality()) === FALSE) {
			return -1;

		} elseif (stripos($query, $b->getLocality()) !== FALSE && stripos($query, $a->getLocality()) === FALSE) {
			return 1;
		}

		return 0;
	}

	protected function compareStreet(Address $a, Address $b, $query)
	{
		if (!$a->getStreetName() && !$b->getStreetName()) {
			return 0;

		} elseif ($a->getStreetName() && !$b->getStreetName()) {
			return -1;

		} elseif ($b->getStreetName() && !$a->getStreetName()) {
			return 1;
		}

		if (stripos($query, $a->getStreetName()) !== FALSE && stripos($query, $b->getStreetName()) === FALSE) {
			return -1;

		} elseif (stripos($query, $b->getStreetName()) !== FALSE && stripos($query, $a->getStreetName()) === FALSE) {
			return 1;
		}

		return 0;
	}

	protected function compareNumbers(Address $a, Address $b, $query)
	{
		// if one of the addresses doesn't even have a number in it,
		$aNumber = UserInputHelpers::matchNumber((string) $a->getStreetNumber());
		$bNumber = UserInputHelpers::matchNumber((string) $b->getStreetNumber());

		$inputNumber = UserInputHelpers::matchStreet($query);
		if ($inputNumber === NULL) {
			return 0;
		}

		if ($this->isNumberFull($inputNumber)) { // prefer more exact
			$aEquals = $this->equalsNumber($aNumber, $inputNumber);
			$bEquals = $this->equalsNumber($bNumber, $inputNumber);
			if ($aEquals && !$bEquals) {
				return -1;
			}
			if ($bEquals && !$aEquals) {
				return 1;
			}
		}

		// prefer more data, but at least one component must equal
		$aPartially = $this->equalsNumberPartially($aNumber, $inputNumber);
		$bPartially = $this->equalsNumberPartially($bNumber, $inputNumber);
		if ($aPartially && !$bPartially) {
			return -1;
		}
		if ($bPartially && !$aPartially) {
			return 1;
		}

		$aHasMore = $this->isNumberFull($aNumber) && !$this->isNumberFull($bNumber);
		$bHasMore = $this->isNumberFull($bNumber) && !$this->isNumberFull($aNumber);
		if ($aHasMore && !$bHasMore) {
			return -1;
		}
		if ($bHasMore && !$aHasMore) {
			return 1;
		}

		return 0;
	}

	/**
	 * @param \stdClass|NULL $number
	 * @return bool
	 */
	protected function isNumberFull($number)
	{
		return !empty($number->hn) && !empty($number->on);
	}

	/**
	 * @param \stdClass|NULL $a
	 * @param \stdClass|NULL $b
	 * @return bool
	 */
	protected function equalsNumber($a, $b)
	{
		if ($a === NULL || $b === NULL) {
			return FALSE;
		}

		if ($a->hn === $b->hn && strtolower($a->on) === strtolower($b->on)) {
			return TRUE;
		}

		if ($a->hn === strtolower($b->on) && strtolower($a->on) === $b->hn) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param \stdClass|NULL $a
	 * @param \stdClass|NULL $b
	 * @return bool
	 */
	protected function equalsNumberPartially($a, $b)
	{
		if ($a === NULL || $b === NULL) {
			return FALSE;
		}

		if (($b->hn !== NULL && $a->hn === $b->hn) || ($b->on !== NULL && strtolower($a->on) === strtolower($b->on))) {
			return TRUE;
		}

		if (($b->hn !== NULL && $a->hn === strtolower($b->on)) || ($b->hn !== NULL && strtolower($a->on) === $b->hn)) {
			return TRUE;
		}

		return FALSE;
	}

}
