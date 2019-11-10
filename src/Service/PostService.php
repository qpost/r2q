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
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use function is_null;
use function json_decode;

class PostService {
	/**
	 * @var HttpClientService $httpClientService
	 */
	private $httpClientService;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var ConfigurationService $configurationService
	 */
	private $configurationService;

	public function __construct(HttpClientService $httpClientService, LoggerInterface $logger, ConfigurationService $configurationService) {
		$this->httpClientService = $httpClientService;
		$this->logger = $logger;
		$this->configurationService = $configurationService;
	}

	/**
	 * @param string $text
	 * @param array $attachments
	 * @param bool $nsfw
	 * @throws Exception
	 */
	public function createPost(string $text, array $attachments, bool $nsfw = false): void {
		$token = $this->configurationService->getAPIToken();
		if (!$token) throw new Exception("Malformed qpost token.");

		$baseURL = $this->configurationService->getBaseURL();
		if (!$baseURL) throw new Exception("Malformed base URL.");

		$response = $this->httpClientService->getClient()->post($baseURL . "/status", [
			RequestOptions::JSON => [
				"message" => $text,
				"attachments" => $attachments,
				"nsfw" => $nsfw
			],
			"headers" => [
				"Authorization" => "Bearer " . $token
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) throw new Exception("Failed to get response body.");

		if ($response->getStatusCode() === 200) return;

		$content = $body->getContents();
		$body->close();

		if (is_null($content)) throw new Exception("Response body is empty.");

		$data = @json_decode($content, true);
		if (!$data) throw new Exception("Failed to parse JSON response body.");

		if (isset($data["error"])) {
			throw new Exception("Failed to post to qpost: " . $data["error"]);
		}
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

	/**
	 * @return ConfigurationService
	 */
	public function getConfigurationService(): ConfigurationService {
		return $this->configurationService;
	}
}