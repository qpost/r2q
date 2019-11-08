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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use function is_array;
use function json_decode;

class ConfigurationService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var KernelInterface $kernel
	 */
	private $kernel;

	/**
	 * @var bool $debug
	 */
	private $debug;

	/**
	 * @var string $baseURL
	 */
	private $baseURL;

	/**
	 * @var string $apiToken
	 */
	private $apiToken;

	/**
	 * @var string[] $subreddits
	 */
	private $subreddits;

	/**
	 * @var bool $ignoreNoImage
	 */
	private $ignoreNoImage;

	public function __construct(LoggerInterface $logger, KernelInterface $kernel) {
		$this->logger = $logger;
		$this->kernel = $kernel;
		$this->debug = $this->kernel->isDebug();

		$this->baseURL = $_ENV["QPOST_BASE_URL"];
		$this->apiToken = $_ENV["QPOST_TOKEN"];
		$this->ignoreNoImage = $_ENV["IGNORE_NO_IMAGE"] === "true";

		$subredditsString = $_ENV["SUBREDDITS"];
		$subreddits = @json_decode($subredditsString, true);
		if ($subreddits && is_array($subreddits)) {
			$this->subreddits = $subreddits;
		} else {
			$this->subreddits = [];
		}
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return KernelInterface
	 */
	public function getKernel(): KernelInterface {
		return $this->kernel;
	}

	/**
	 * @return bool
	 */
	public function isDebug(): bool {
		return $this->debug;
	}

	/**
	 * @return string
	 */
	public function getBaseURL(): string {
		return $this->baseURL;
	}

	/**
	 * @return string
	 */
	public function getAPIToken(): string {
		return $this->apiToken;
	}

	/**
	 * @return string[]
	 */
	public function getSubreddits(): array {
		return $this->subreddits;
	}

	/**
	 * @return bool
	 */
	public function ignoresNoImage(): bool {
		return $this->ignoreNoImage;
	}
}