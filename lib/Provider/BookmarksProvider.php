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


namespace OCA\Bookmarks_FullTextSearch\Provider;


use Exception;
use OCA\Bookmarks_FullTextSearch\Exceptions\WebpageIsNotIndexableException;
use OCA\Bookmarks_FullTextSearch\Model\BookmarksDocument;
use OCA\Bookmarks_FullTextSearch\Service\BookmarksService;
use OCA\Bookmarks_FullTextSearch\Service\ConfigService;
use OCA\Bookmarks_FullTextSearch\Service\MiscService;
use OCA\Bookmarks_FullTextSearch\Service\SearchService;
use OCA\Bookmarks_FullTextSearch\Service\TagsService;
use OCP\AppFramework\QueryException;
use OCP\FullTextSearch\IFullTextSearchPlatform;
use OCP\FullTextSearch\IFullTextSearchProvider;
use OCP\FullTextSearch\Model\IIndex;
use OCP\FullTextSearch\Model\IIndexOptions;
use OCP\FullTextSearch\Model\IndexDocument;
use OCP\FullTextSearch\Model\IRunner;
use OCP\FullTextSearch\Model\ISearchRequest;
use OCP\FullTextSearch\Model\ISearchResult;
use OCP\FullTextSearch\Model\SearchTemplate;
use OCP\IL10N;


class BookmarksProvider implements IFullTextSearchProvider {


	const BOOKMARKS_PROVIDER_ID = 'bookmarks';


	/** @var IL10N */
	private $l10n;

	/** @var ConfigService */
	private $configService;

	/** @var BookmarksService */
	private $bookmarksService;

	/** @var TagsService */
	private $tagsService;

	/** @var SearchService */
	private $searchService;

	/** @var MiscService */
	private $miscService;


	/** @var IRunner */
	private $runner;

	/** @var IIndexOptions */
	private $indexOptions;


	public function __construct(
		IL10N $l10n, ConfigService $configService, BookmarksService $bookmarksService,
		TagsService $tagsService, SearchService $searchService, MiscService $miscService
	) {
		$this->l10n = $l10n;
		$this->configService = $configService;
		$this->bookmarksService = $bookmarksService;
		$this->tagsService = $tagsService;
		$this->searchService = $searchService;
		$this->miscService = $miscService;
	}


	/**
	 * return unique id of the provider
	 */
	public function getId(): string {
		return self::BOOKMARKS_PROVIDER_ID;
	}


	/**
	 * return name of the provider
	 */
	public function getName(): string {
		return 'Bookmarks';
	}


//	/**
//	 * @return string
//	 */
//	public function getVersion() {
//		return $this->configService->getAppValue('installed_version');
//	}
//
//
//	/**
//	 * @return string
//	 */
//	public function getAppId() {
//		return Application::APP_NAME;
//	}
//

	/**
	 * @return array
	 */
	public function getConfiguration(): array {
		return $this->configService->getConfig();
	}


	/**
	 * @param IRunner $runner
	 */
	public function setRunner(IRunner $runner) {
		$this->runner = $runner;
	}


	/**
	 * @param IIndexOptions $options
	 */
	public function setIndexOptions(IIndexOptions $options) {
		$this->indexOptions = $options;
	}


	public function getSearchTemplate(): SearchTemplate {

		$template = new SearchTemplate('icon-fts-bookmarks', 'fulltextsearch');

//		$template->addPanelOption(
//			new SearchOption(
//				'files_within_dir', $this->l10n->t('Within current directory'),
//				SearchOption::CHECKBOX
//			)
//		);
//
//		$template->addPanelOption(
//			new SearchOption(
//				'files_local', $this->l10n->t('Within local files'),
//				SearchOption::CHECKBOX
//			)
//		);
//		$template->addNavigationOption(
//			new SearchOption(
//				'files_local', $this->l10n->t('Local files'),
//				SearchOption::CHECKBOX
//			)
//		);
//
//		if ($this->configService->getAppValue(ConfigService::FILES_EXTERNAL) === '1') {
//			$template->addPanelOption(
//				new SearchOption(
//					'files_external', $this->l10n->t('Within external files'),
//					SearchOption::CHECKBOX
//				)
//			);
//			$template->addNavigationOption(
//				new SearchOption(
//					'files_external', $this->l10n->t('External files'), SearchOption::CHECKBOX
//				)
//			);
//		}
//
//		if ($this->configService->getAppValue(ConfigService::FILES_GROUP_FOLDERS) === '1') {
//			$template->addPanelOption(
//				new SearchOption(
//					'files_group_folders', $this->l10n->t('Within group folders'),
//					SearchOption::CHECKBOX
//				)
//			);
//			$template->addNavigationOption(
//				new SearchOption(
//					'files_group_folders', $this->l10n->t('Group folders'),
//					SearchOption::CHECKBOX
//				)
//			);
//		}
//
//		$template->addPanelOption(
//			new SearchOption(
//				'files_extension', $this->l10n->t('Filter by extension'), SearchOption::INPUT,
//				SearchOption::INPUT_SMALL, 'txt'
//			)
//		);
//		$template->addNavigationOption(
//			new SearchOption(
//				'files_extension', $this->l10n->t('Extension'), SearchOption::INPUT,
//				SearchOption::INPUT_SMALL, 'txt'
//			)
//		);

		return $template;
	}



//		/**
//	 * @return array
//	 */
//	public function getOptionsTemplate() {
//		return [
//			'navigation' => [
//				'icon' => 'icon-fts-bookmarks',
//				//				'options' => [
//				//					[
//				//						'name'  => 'bookmarks_tags',
//				//						'title' => 'Filter tags',
//				//						'type'  => 'tags',
//				//						'list'  => $this->tagsService->getAllForUser()
//				//					]
//				//				]
//			]
//		];
//	}


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
			throw new QueryException('bookmarks app not available');
		}
	}


	/**
	 * returns all indexable document for a user.
	 * There is no need to fill the document with content at this point.
	 *
	 * $platform is provided if the mapping needs to be changed.
	 *
	 * @param string $userId
	 *
	 * @return IndexDocument[]
	 */
	public function generateIndexableDocuments(string $userId): array {
		$bookmarks = $this->bookmarksService->getBookmarksFromUser($userId);

		return $bookmarks;
	}


	/**
	 * @param IndexDocument $document
	 */
	public function fillIndexDocument(IndexDocument $document) {
		/** @var BookmarksDocument $document */
		try {
			$this->updateRunnerInfoArray(
				[
					'info'    => $document->getSource(),
					'title'   => '',
					'content' => ''
				]
			);

			/** @var BookmarksDocument $document */
			$this->bookmarksService->updateDocumentFromBookmarksDocument($document);
		} catch (Exception $e) {
			$this->manageErrorException($document, $e);
		}

	}


//	/**
//	 * @param IndexDocument $document
//	 */
//	public function fillIndexDocument(IndexDocument $document) {
//		try {
//			$this->updateRunnerInfo('info', $document->getSource());
//
//			/** @var BookmarksDocument $document */
//			$this->bookmarksService->updateDocumentFromBookmarksDocument($document);
//
//		} catch (Exception $e) {
//			$this->manageErrorException($document, $e);
//		}
//	}


	/**
	 * @param IndexDocument $document
	 *
	 * @return bool
	 */
	public function isDocumentUpToDate(IndexDocument $document): bool {
		return $this->bookmarksService->isDocumentUpToDate($document);
	}


	/**
	 * @param IIndex $index
	 *
	 * @return IndexDocument
	 * @throws WebpageIsNotIndexableException
	 */
	public function updateDocument(IIndex $index): IndexDocument {
		/** @var BookmarksDocument $document */
		$document = $this->bookmarksService->updateDocument($index);
		$this->updateRunnerInfo('info', $document->getSource());

		return $document;
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onInitializingIndex(IFullTextSearchPlatform $platform) {
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onResettingIndex(IFullTextSearchPlatform $platform) {
	}


	/**
	 * not used yet
	 */
	public function unloadProvider() {
	}


	/**
	 * before a search, improve the request
	 *
	 * @param ISearchRequest $request
	 */
	public function improveSearchRequest(ISearchRequest $request) {
		$this->searchService->improveSearchRequest($request);
	}


	/**
	 * after a search, improve results
	 *
	 * @param ISearchResult $searchResult
	 */
	public function improveSearchResult(ISearchResult $searchResult) {
		foreach ($searchResult->getDocuments() as $document) {
			/** @var BookmarksDocument $document */
			$document->setLink($document->getSource());
			$document->setInfo('source', $document->getSource());
		}
	}


	/**
	 * @param IndexDocument $document
	 * @param Exception $e
	 */
	private function manageErrorException(IndexDocument $document, Exception $e) {
		$document->getIndex()
				 ->addError($e->getMessage(), get_class($e), IIndex::ERROR_SEV_3);
		$this->updateNewIndexError(
			$document->getIndex(), $e->getMessage(), get_class($e), IIndex::ERROR_SEV_3
		);
	}


	/**
	 * @param IIndex $index
	 * @param string $message
	 * @param string $exception
	 * @param int $sev
	 */
	private function updateNewIndexError(IIndex $index, string $message, string $exception, int $sev
	) {
		if ($this->runner === null) {
			return;
		}

		$this->runner->newIndexError($index, $message, $exception, $sev);
	}


	/**
	 * @param string $info
	 * @param string $value
	 */
	private function updateRunnerInfo(string $info, string $value) {
		if ($this->runner === null) {
			return;
		}

		$this->runner->setInfo($info, $value);
	}


	/**
	 * @param array $data
	 */
	private function updateRunnerInfoArray(array $data) {
		if ($this->runner === null) {
			return;
		}

		$this->runner->setInfoArray($data);
	}

}

