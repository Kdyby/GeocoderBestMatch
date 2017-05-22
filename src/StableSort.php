<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\BestMatch;

use Closure;

/**
 * @internal
 */
class StableSort
{

	/**
	 * @see http://stackoverflow.com/a/40648960/602899
	 *
	 * @internal
	 * @param array $array
	 * @param \Closure $cmp
	 * @return array
	 */
	public static function sort($array, Closure $cmp)
	{
		$index = 0;
		foreach ($array as &$a) {
			$a = [$index++, $a];
		}
		unset($a);
		$result = usort($array, function ($a, $b) use ($cmp) {
			$result = call_user_func($cmp, $a[1], $b[1]);
			return ($result === 0) ? $a[0] - $b[0] : $result;
		});
		foreach ($array as &$b) {
			$b = $b[1];
		}
		unset($b);
		return $array;
	}

}
