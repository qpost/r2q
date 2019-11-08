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

use r2q\Post;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function time;

class DatabaseService {
	private $path = __DIR__ . "/../../data.json";

	/**
	 * @var Post[] $posts
	 */
	private $posts;

	public function __construct() {
		// Load data and create file if it does not exist

		$this->loadData();
		$this->removeStaleData();
		$this->saveData();
	}

	public function loadData(): void {
		if (!file_exists($this->path)) {
			$this->posts = [];
		} else {
			$this->posts = json_decode(file_get_contents($this->path));
		}
	}

	public function saveData(): void {
		file_put_contents($this->path, json_encode($this->posts));
	}

	public function addPost(Post $post): void {
		$this->posts[] = $post;
	}

	public function wasPosted(string $id): bool {
		foreach ($this->posts as $post) {
			if ($post->id === $id) return true;
		}

		return false;
	}

	public function removeStaleData(): void {
		$results = [];

		$maxLifetime = 90 * 24 * 60 * 60; // 90 days

		foreach ($this->posts as $post) {
			if ($post->time >= (time() - $maxLifetime)) {
				$results[] = $post;
			}
		}

		$this->posts = $results;
	}
}