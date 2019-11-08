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

use GuzzleHttp\Client;

class HttpClientService {
	private $client;

	public function __construct() {
		$this->client = new Client([
			"timeout" => 5,
			"headers" => [
				"User-Agent" => "r2q by Gigadrive (https://gitlab.com/Gigadrive/qpost/r2q)"
			]
		]);
	}

	/**
	 * @return Client
	 */
	public function getClient(): Client {
		return $this->client;
	}
}