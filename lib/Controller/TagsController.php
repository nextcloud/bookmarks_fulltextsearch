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

namespace OCA\Bookmarks_FullTextSearch\Controller;

use OCA\Bookmarks_FullTextSearch\AppInfo\Application;
use OCA\Bookmarks_FullTextSearch\Service\MiscService;
use OCA\Bookmarks_FullTextSearch\Service\TagsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TagsController extends Controller {

	/** @var TagsService */
	private $tagsService;

	/** @var MiscService */
	private $miscService;


	/**
	 * ApiController constructor.
	 *
	 * @param IRequest $request
	 * @param TagsService $tagsService
	 * @param MiscService $miscService
	 */
	public function __construct(
		IRequest $request, TagsService $tagsService, MiscService $miscService
	) {
		parent::__construct(Application::APP_NAME, $request);
		$this->tagsService = $tagsService;
		$this->miscService = $miscService;
	}


	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 *
	 * @param string $search
	 *
	 * @return DataResponse
	 */
	public function search($search) {
		$tags = $this->tagsService->searchTags($search);

		return new DataResponse($tags, Http::STATUS_OK);
	}

}