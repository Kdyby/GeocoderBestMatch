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

interface AddressComparator
{

	/**
	 * @param \Geocoder\Model\Address $a
	 * @param \Geocoder\Model\Address $b
	 * @param string $geocoderQuery
	 * @return int
	 */
	public function compare(Address $a, Address $b, $geocoderQuery);

}
