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

use function base64_encode;
use function file_get_contents;
use function getimagesizefromstring;
use function str_replace;
use function stream_context_create;

class ImageService {
	private $util;

	public function __construct(UtilService $util) {
		$this->util = $util;
	}

	/**
	 * @param string $url
	 * @return string|null
	 */
	public function downloadURL(string $url): ?string {
		$file = file_get_contents($url, false, stream_context_create([
			"http" => [
				"method" => "GET",
				"header" => "User-Agent: r2q by Gigadrive (https://gitlab.com/Gigadrive/qpost/r2q)"
			]
		]));

		if (!$file) return null;
		if (!getimagesizefromstring($file)) return null;

		return base64_encode($file);
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	public function getImageURLFromPostData(array $data): ?string {
		if (!isset($data["url"])) return null;

		$url = $data["url"];

		// reddit GIF (results in encoding error)
		if (($this->util->startsWith($url, "https://i.redd.it/") || $this->util->startsWith($url, "http://i.redd.it/")) && $this->util->endsWith($url, ".gif")) {
			return null;
		}

		// direct image URL
		if ($this->util->endsWith($url, ".png") || $this->util->endsWith($url, ".gif") || $this->util->endsWith($url, ".jpg") || $this->util->endsWith($url, ".jpeg")) {
			return $url;
		}

		// imgur GIF
		if ($this->util->endsWith($url, ".gifv")) {
			return str_replace(".gifv", ".gif", $url);
		}

		// imgur page URL (SSL off)
		if ($this->util->startsWith($url, "http://imgur.com/")) {
			return "https://i.imgur.com/" . str_replace("http://imgur.com/", "", $url) . ".jpg";
		}

		// imgur page URL (SSL on)
		if ($this->util->startsWith($url, "https://imgur.com/")) {
			return "https://i.imgur.com/" . str_replace("https://imgur.com/", "", $url) . ".jpg";
		}

		return null;
	}
}