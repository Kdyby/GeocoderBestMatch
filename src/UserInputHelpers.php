<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\BestMatch;

class UserInputHelpers
{

	// regexes for parsing address
	const RE_STREET = '(?P<street>(?:[0-9]+(?=[^/,]+))?[^/,0-9]+(?<![\s\,]))'; // '(?P<street>([0-9]+(?=[^/,]+))?[^/,0-9]+(?=[a-z\s\,]))';
	const RE_NUMBER = '(?P<number>[0-9]+(?:\/[0-9]+)?[a-z]?)';
	const RE_CITY = '(?P<city>(?:(?P<city_name>[^,-]+(?<!\s))(?:(?<!\s)-(?!\s)(?P<city_part>\\5[^,]+(?<!\s)))?)|(?:[^,]+(?<!\s)))';
	const RE_POSTAL_CODE = '(?P<psc>\d{3}\s?\d{2})';

	/**
	 * @param string $number
	 * @return \stdClass|NULL
	 */
	public static function matchNumber($number)
	{
		return (trim($number) !== '' && preg_match('~' . self::RE_NUMBER . '~i', trim($number), $m))
			? self::normalizeNumber($m)
			: NULL;
	}

	/**
	 * @param array $m
	 * @return \stdClass
	 */
	public static function normalizeNumber(array $m)
	{
		$m = (object) $m;

		if (!empty($m->number)) {
			if (strpos($m->number, '/') !== FALSE) {
				list($m->hn, $m->on) = explode('/', $m->number, 2);
			} elseif (is_numeric($m->number)) {
				$m->hn = $m->number;
			} else { // 3a
				$m->on = $m->number;
			}

		} else {
			$m->number = NULL;
		}

		$m->hn = !empty($m->hn) ? $m->hn : NULL;
		$m->on = !empty($m->on) ? $m->on : NULL;

		return $m;
	}

	/**
	 * @param string $street
	 * @return \stdClass|NULL
	 */
	public static function matchStreet($street)
	{
		if (!preg_match('~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,.*)?~i', trim($street), $m)) {
			return NULL;
		}

		$n = self::normalizeNumber($m);

		return (object) [
			'street' => $m['street'],
			'number' => $n->number,
			'on' => $n->on,
			'hn' => $n->hn,
		];
	}

	/**
	 * @param string $postalCode
	 * @return \stdClass|null
	 */
	public static function matchPostalCode($postalCode)
	{
		$postalCode = trim($postalCode);
		if (!preg_match('~^' . self::RE_POSTAL_CODE . '\s*' . str_replace('\\5', '\\3', self::RE_CITY) . '?\z~i', $postalCode, $m)) {
			if (!preg_match('~^' . str_replace('\\5', '\\2', self::RE_CITY) . '?\s*' . self::RE_POSTAL_CODE . '\z~i', $postalCode, $m)) {
				return NULL;
			}
		}

		return (object) [
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			'postalCode' => self::removeWhitespace(!empty($m['postalCode']) ? $m['postalCode'] : NULL),
			'country' => self::matchCountry($postalCode),
		];
	}

	/**
	 * @param string $address
	 * @return \stdClass|NULL
	 */
	public static function matchAddress($address)
	{
		if (!preg_match('~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,\s?' . self::RE_POSTAL_CODE . '|\,)(?:\s?' . self::RE_CITY . ')\,?~i', trim($address), $m)) {
			return NULL;
		}

		$n = self::normalizeNumber($m);

		return (object) [
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			// 'cityPart' => !empty($m['city_part']) ? $m['city_part'] : NULL,
			'street' => !empty($m['street']) ? $m['street'] : NULL,
			'number' => $n->number,
			'on' => $n->on,
			'hn' => $n->hn,
			'postalCode' => self::removeWhitespace(!empty($m['postalCode']) ? $m['postalCode'] : NULL),
			'country' => self::matchCountry($address),
		];
	}

	/**
	 * @param string $address
	 * @return \stdClass|NULL
	 */
	public static function matchFullAddress($address)
	{
		if (!preg_match('~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,\s?' . self::RE_POSTAL_CODE . '|\,)(?:\s?' . self::RE_CITY . ')\,~i', trim($address), $m)) {
			return NULL;
		}

		if (preg_match('~^[\d\s]{5,6}$~', $m['city']) && empty($m['postalCode'])) { // Brno 2, 602 00, Česká republika
			$m['postalCode'] = $m['city'];
			$m['city'] = $m['street'];
			unset($m['city_name']);
		}

		$n = self::normalizeNumber($m);

		return (object) [
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			// 'cityPart' => !empty($m['city_part']) ? $m['city_part'] : NULL,
			'street' => !empty($m['street']) ? $m['street'] : NULL,
			'number' => $n->number,
			'on' => $n->on,
			'hn' => $n->hn,
			'postalCode' => self::removeWhitespace(!empty($m['postalCode']) ? $m['postalCode'] : NULL),
			'country' => self::matchCountry($address),
		];
	}

	/**
	 * @param string $address
	 * @return string|NULL
	 */
	public static function matchCountry($address)
	{
		return preg_match('~.*,\s*(?P<country>(?:Česká Republika|Slovenská Republika|Slovensko|Česko))$~iu', trim($address), $m)
			? $m['country']
			: NULL;
	}

	/**
	 * @param string $value
	 * @return string|NULL
	 */
	private static function removeWhitespace($value)
	{
		return preg_replace('~\s~', '', trim($value));
	}

}
