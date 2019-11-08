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

use Exception;
use Psr\Log\LoggerInterface;
use function json_decode;

class RedditService {
	private $httpClientService;
	private $logger;

	public function __construct(HttpClientService $httpClientService, LoggerInterface $logger) {
		$this->httpClientService = $httpClientService;
		$this->logger = $logger;
	}

	/**
	 * @param string $subreddit
	 * @param int $limit
	 * @return array
	 * @throws Exception
	 */
	public function getHotPosts(string $subreddit, int $limit = 20): array {
		$url = "https://www.reddit.com/r/" . $subreddit . "/hot.json";

		$response = $this->httpClientService->getClient()->get($url);
		if (!$response) throw new Exception("Request failed.");

		$body = $response->getBody();
		if (!$body) throw new Exception("Failed to fetch response body.");

		$content = $body->getContents();
		$body->close();
		if (!$content) throw new Exception("Failed to read response body content.");

		$json = @json_decode($content, true);
		if (!$json) throw new Exception("Failed to parse response body JSON.");

		if (!isset($json["data"])) throw new Exception("Malformed JSON response (data missing).");
		$data = $json["data"];

		if (!isset($data["children"])) throw new Exception("Malformed JSON response (children missing).");
		$children = $data["children"];

		if (!is_array($children)) throw new Exception("Malformed JSON response (children is not an array).");

		$results = [];

		foreach ($children as $child) {
			if (isset($child["data"])) {
				$results[] = $child["data"];
			}
		}

		return $results;
	}

	/**
	 * @return HttpClientService
	 */
	public function getHttpClientService(): HttpClientService {
		return $this->httpClientService;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
}