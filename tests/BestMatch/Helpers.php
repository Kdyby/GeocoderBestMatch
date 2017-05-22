<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyTests\Geocoder\BestMatch;

use Geocoder\Model\AddressFactory;

class Helpers
{

	/**
	 * @return \Geocoder\Model\Address
	 */
	public static function createAddress($city, $street, $houseNumber = NULL, $orientationNumber = NULL, $postalCode = NULL)
	{
		$factory = new AddressFactory();
		return $factory->createFromArray([
			[
				'locality' => $city,
				'streetName' => $street,
				'streetNumber' => implode('/', array_filter([$houseNumber, $orientationNumber])),
				'postalCode' => $postalCode,
			],
		])->first();
	}

}
