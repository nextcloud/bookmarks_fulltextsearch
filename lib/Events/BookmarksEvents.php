<?php

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

namespace OCA\Bookmarks_FullTextSearch\Events;

use OCA\Bookmarks_FullTextSearch\Model\BookmarksDocument;
use OCA\Bookmarks_FullTextSearch\Service\BookmarksService;
use OCA\Bookmarks_FullTextSearch\Service\MiscService;
use OCA\FullTextSearch\Api\v1\FullTextSearch;
use OCA\FullTextSearch\Model\Index;
use OCP\AppFramework\QueryException;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;

class BookmarksEvents {


	/** @var string */
	private $userId;

	/** @var BookmarksService */
	private $bookmarksService;

	/** @var MiscService */
	private $miscService;

	/**
	 * FilesEvents constructor.
	 *
	 * @param string $userId
	 * @param BookmarksService $bookmarksService
	 * @param MiscService $miscService
	 */
	public function __construct($userId, BookmarksService $bookmarksService, MiscService $miscService) {

		$this->userId = $userId;
		$this->bookmarksService = $bookmarksService;
		$this->miscService = $miscService;
	}


}




