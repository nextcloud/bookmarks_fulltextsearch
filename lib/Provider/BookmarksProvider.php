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

namespace OCA\Bookmarks_FullTextSearch\Provider;

use OCA\Bookmarks_FullTextSearch\AppInfo\Application;
use OCA\Bookmarks_FullTextSearch\Model\BookmarksDocument;
use OCA\Bookmarks_FullTextSearch\Service\BookmarksService;
use OCA\Bookmarks_FullTextSearch\Service\ConfigService;
use OCA\Bookmarks_FullTextSearch\Service\ElasticSearchService;
use OCA\Bookmarks_FullTextSearch\Service\MiscService;
use OCA\Bookmarks_FullTextSearch\Service\SearchService;
use OCA\Bookmarks_FullTextSearch\Service\TagsService;
use OCA\FullTextSearch\IFullTextSearchPlatform;
use OCA\FullTextSearch\IFullTextSearchProvider;
use OCA\FullTextSearch\Model\Index;
use OCA\FullTextSearch\Model\IndexDocument;
use OCA\FullTextSearch\Model\IndexOptions;
use OCA\FullTextSearch\Model\Runner;
use OCA\FullTextSearch\Model\SearchRequest;
use OCA\FullTextSearch\Model\SearchResult;
use OCP\AppFramework\QueryException;


class BookmarksProvider implements IFullTextSearchProvider {


	const BOOKMARKS_PROVIDER_ID = 'bookmarks';

	/** @var ConfigService */
	private $configService;

	/** @var BookmarksService */
	private $bookmarksService;

	/** @var TagsService */
	private $tagsService;

	/** @var SearchService */
	private $searchService;

	/** @var ElasticSearchService */
	private $elasticSearchService;

	/** @var MiscService */
	private $miscService;


	/** @var Runner */
	private $runner;

	/** @var IndexOptions */
	private $indexOptions;


	/**
	 * return unique id of the provider
	 */
	public function getId() {
		return self::BOOKMARKS_PROVIDER_ID;
	}


	/**
	 * return name of the provider
	 */
	public function getName() {
		return 'Bookmarks';
	}


	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->configService->getAppValue('installed_version');
	}


	/**
	 * @return string
	 */
	public function getAppId() {
		return Application::APP_NAME;
	}


	/**
	 * @return array
	 */
	public function getConfiguration() {
		return $this->configService->getConfig();
	}


	/**
	 * @param Runner $runner
	 */
	public function setRunner(Runner $runner) {
		$this->runner = $runner;
	}


	/**
	 * @param IndexOptions $options
	 */
	public function setIndexOptions($options) {
		$this->indexOptions = $options;
	}


	/**
	 * @return array
	 */
	public function getOptionsTemplate() {
		return [
			'navigation' => [
				'icon' => 'icon-fts-bookmarks',
				//				'options' => [
				//					[
				//						'name'  => 'bookmarks_tags',
				//						'title' => 'Filter tags',
				//						'type'  => 'tags',
				//						'list'  => $this->tagsService->getAllForUser()
				//					]
				//				]
			]
		];
	}


	/**
	 * called when loading all providers.
	 *
	 * Loading some containers.
	 *
	 * @throws QueryException
	 */
	public function loadProvider() {
		$appManager = \OC::$server->getAppManager();
		if (!$appManager->isInstalled('bookmarks')) {
			throw new QueryException();
		}

		$app = new Application();

		$container = $app->getContainer();
		$this->configService = $container->query(ConfigService::class);
		$this->bookmarksService = $container->query(BookmarksService::class);
		$this->tagsService = $container->query(TagsService::class);
		$this->searchService = $container->query(SearchService::class);
		$this->elasticSearchService = $container->query(ElasticSearchService::class);
		$this->miscService = $container->query(MiscService::class);
	}


	/**
	 * returns all indexable document for a user.
	 * There is no need to fill the document with content at this point.
	 *
	 * $platform is provided if the mapping needs to be changed.
	 *
	 * @param string $userId
	 *
	 * @return BookmarksDocument[]
	 */
	public function generateIndexableDocuments($userId) {
		$bookmarks = $this->bookmarksService->getBookmarksFromUser($this->runner, $userId);

		return $bookmarks;
	}


	/**
	 * generate documents prior to the indexing.
	 * throw NoResultException if no more result
	 *
	 * @param IndexDocument[] $chunk
	 *
	 * @return IndexDocument[]
	 */
	public function fillIndexDocuments($chunk) {

		/** @var BookmarksDocument[] $chunk */
		$result = $this->bookmarksService->generateDocuments($chunk);

		return $result;
	}


	/**
	 * @param IndexDocument $document
	 *
	 * @return bool
	 */
	public function isDocumentUpToDate($document) {
		return $this->bookmarksService->isDocumentUpToDate($document);
	}


	/**
	 * @param Index $index
	 *
	 * @return BookmarksDocument|null
	 */
	public function updateDocument(Index $index) {
		return $this->bookmarksService->updateDocument($index);
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onInitializingIndex(IFullTextSearchPlatform $platform) {
		$this->elasticSearchService->onInitializingIndex($platform);
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onResettingIndex(IFullTextSearchPlatform $platform) {
		$this->elasticSearchService->onResettingIndex($platform);
	}


	/**
	 * not used yet
	 */
	public function unloadProvider() {
	}


	/**
	 * before a search, improve the request
	 *
	 * @param SearchRequest $request
	 */
	public function improveSearchRequest(SearchRequest $request) {
		$this->searchService->improveSearchRequest($request);
	}


	/**
	 * after a search, improve results
	 *
	 * @param SearchResult $searchResult
	 */
	public function improveSearchResult(SearchResult $searchResult) {
		foreach ($searchResult->getDocuments() as $document) {
			$document->setLink($document->getSource());
			$document->setInfo('source', $document->getSource());
		}
	}


}