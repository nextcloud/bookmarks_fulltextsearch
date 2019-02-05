<?php
declare(strict_types=1);


/**
 * Bookmarks_FullTextSearch - Indexing bookmarks
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Bookmarks_FullTextSearch\Service;


use OCA\Bookmarks\Bookmarks;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\QueryException;


class TagsService {


	/** @var string */
	private $userId;

	/** @var MiscService */
	private $miscService;

	/** @var Bookmarks */
	private $bookmarksClass;


	/**
	 * TagsService constructor.
	 *
	 * @param string $userId
	 * @param IAppContainer $container
	 * @param MiscService $miscService
	 */
	public function __construct($userId, IAppContainer $container, MiscService $miscService) {
		$this->userId = $userId;
		$this->miscService = $miscService;

		try {
			$this->bookmarksClass = $container->query(Bookmarks::class);
		} catch (QueryException $e) {
			/** we do nothing */
		}
	}


	/**
	 * @return array
	 */
	public function getAllForUser(): array {
		$result = [];
		$allTags = $this->bookmarksClass->findTags($this->userId);
		foreach ($allTags as $tag) {
			$result[] = $tag['tag'];
		}

		return $result;
	}


	/**
	 * @param string $search
	 *
	 * @return array
	 */
	public function search(string $search): array {
		$result = [];
		$allTags = $this->bookmarksClass->findTags($this->userId);

		foreach ($allTags as $tag) {
			$tagName = $tag['tag'];
			if (strpos($tagName, $search) === 0) {
				$result[] = $tagName;
			}
		}

		return $result;
	}


}
