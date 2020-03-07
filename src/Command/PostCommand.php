<?php
/**
 * Copyright (C) 2020 Gigadrive - All rights reserved.
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

namespace r2q\Command;

use Exception;
use Psr\Log\LoggerInterface;
use r2q\Post;
use r2q\Service\ConfigurationService;
use r2q\Service\DatabaseService;
use r2q\Service\HttpClientService;
use r2q\Service\ImageService;
use r2q\Service\PostService;
use r2q\Service\RedditService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function rand;
use function time;

class PostCommand extends Command {
	protected static $defaultName = "r2q:post";

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var ConfigurationService $configurationService
	 */
	private $configurationService;

	/**
	 * @var HttpClientService $httpClientService
	 */
	private $httpClientService;

	/**
	 * @var RedditService $redditService
	 */
	private $redditService;

	/**
	 * @var DatabaseService $databaseService
	 */
	private $databaseService;

	/**
	 * @var ImageService $imageService
	 */
	private $imageService;

	/**
	 * @var PostService $postService
	 */
	private $postService;

	public function __construct(LoggerInterface $logger, ConfigurationService $configurationService, HttpClientService $httpClientService, RedditService $redditService, DatabaseService $databaseService, ImageService $imageService, PostService $postService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->configurationService = $configurationService;
		$this->httpClientService = $httpClientService;
		$this->redditService = $redditService;
		$this->databaseService = $databaseService;
		$this->imageService = $imageService;
		$this->postService = $postService;
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

	/**
	 * @return HttpClientService
	 */
	public function getHttpClientService(): HttpClientService {
		return $this->httpClientService;
	}

	/**
	 * @return RedditService
	 */
	public function getRedditService(): RedditService {
		return $this->redditService;
	}

	protected function configure() {
		$this
			->setDescription("Fetches the next reddit post and posts it to qpost.")
			->setHelp("Fetches the next reddit post and posts it to qpost.");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$subreddits = $this->configurationService->getSubreddits();
		if (!$subreddits || count($subreddits) === 0) throw new Exception("Failed to load subreddits.");

		$subredditName = $subreddits[rand(0, count($subreddits) - 1)];

		$submissions = $this->redditService->getHotPosts($subredditName);
		if (!$submissions) throw new Exception("Failed to load submissions.");

		if (count($submissions) === 0) throw new Exception("No new submissions found.");

		foreach ($submissions as $submission) {
			$id = $submission["id"];
			$url = $submission["url"];
			$over18 = $submission["over_18"];
			$spoiler = $submission["spoiler"];

			if ($spoiler) {
				$output->writeln("Skipping " . $id . " because it was marked as a spoiler.");
				continue;
			}

			if ($this->databaseService->wasPosted($id)) {
				$output->writeln("Skipping " . $id . " because it was already posted.");
				continue;
			}

			if (!$url) {
				$output->writeln("Skipping " . $id . " because the URL could not be loaded.");
				continue;
			}

			$imageURL = $this->imageService->getImageURLFromPostData($submission);
			if (!$imageURL) {
				$output->writeln("Skipping " . $id . " because the image could not be loaded.");
				continue;
			}

			$base64 = $this->imageService->downloadURL($imageURL);
			if (!$base64) {
				$output->writeln("Skipping " . $id . " because it could not be downloaded.");
				continue;
			}

			$postData = new Post();
			$postData->id = $id;
			$postData->time = time();

			$this->databaseService->addPost($postData);
			$this->databaseService->saveData();

			$this->postService->createPost($submission["title"] . " - https://redd.it/" . $id, [$base64], $over18);

			$output->writeln("Posted " . $id);

			return;
		}

		throw new Exception("No new submissions found.");
	}
}