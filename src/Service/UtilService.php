<?php
/**
 * Copyright (C) 2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace r2q\Service;

use function strlen;
use function substr;

class UtilService {
	/**
	 * Checks whether a string contains another string
	 *
	 * @access public
	 * @param string $string The full string
	 * @param string $check The substring to be checked
	 * @return bool
	 */
	public function contains($string, $check): bool {
		return strpos($string, $check) !== false;
	}

	/**
	 * Gets whether a string starts with another
	 *
	 * @access public
	 * @param string $string The string in subject
	 * @param string $start The string to be checked whether it is the start of $string
	 * @param bool $ignoreCase If true, the case of the strings won't affect the result
	 * @return bool
	 */
	public function startsWith(string $string, string $start, bool $ignoreCase = false): bool {
		if (strlen($start) <= strlen($string)) {
			if ($ignoreCase == true) {
				return substr($string, 0, strlen($start)) == $start;
			} else {
				return strtolower(substr($string, 0, strlen($start))) == strtolower($start);
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets whether a string ends with another
	 *
	 * @access public
	 * @param string $string The string in subject
	 * @param string $end The string to be checked whether it is the end of $string
	 * @return bool
	 */
	public function endsWith(string $string, string $end): bool {
		$length = strlen($end);
		return $length === 0 ? true : (substr($end, -$length) === $end);
	}
}