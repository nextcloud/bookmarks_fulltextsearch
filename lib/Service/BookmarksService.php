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

namespace OCA\Bookmarks_FullTextSearch\Service;


use Exception;
use OCA\Bookmarks\Controller\Lib\Bookmarks;
use OCA\Bookmarks_FullTextSearch\Exceptions\WebpageIsNotIndexableException;
use OCA\Bookmarks_FullTextSearch\Model\BookmarksDocument;
use OCA\FullTextSearch\Model\DocumentAccess;
use OCA\FullTextSearch\Model\Index;
use OCA\FullTextSearch\Model\IndexDocument;
use OCA\FullTextSearch\Model\Runner;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\QueryException;

class BookmarksService {

	const DOCUMENT_TYPE = 'bookmarks';


	/** @var ConfigService */
	private $configService;

	/** @var MiscService */
	private $miscService;

	/** @var Bookmarks */
	private $bookmarksClass;


	/**
	 * BookmarksService constructor.
	 *
	 * @param IAppContainer $container
	 * @param ConfigService $configService
	 * @param MiscService $miscService
	 */
	public function __construct(
		IAppContainer $container, ConfigService $configService, MiscService $miscService
	) {
		$this->configService = $configService;
		$this->miscService = $miscService;

		try {
			$this->bookmarksClass = $container->query(Bookmarks::class);
		} catch (QueryException $e) {
			/** we do nothing */
		}
	}


	/**
	 * @param Runner $runner
	 * @param string $userId
	 *
	 * @return BookmarksDocument[]
	 */
	public function getBookmarksFromUser(Runner $runner, $userId) {

		$bookmarks = $this->bookmarksClass->findBookmarks($userId, 0, 'id', [], false, -1);

		$documents = [];
		foreach ($bookmarks as $bookmark) {
			$document = $this->generateBookmarksDocumentFromBookmark($bookmark, $userId);

			$documents[] = $document;
		}

		return $documents;
	}


	/**
	 * @param BookmarksDocument[] $documents
	 *
	 * TODO - update $document with a error status instead of just ignore !
	 *
	 * @return array
	 */
	public function generateDocuments($documents) {

		$index = [];
		foreach ($documents as $document) {
			if (!($document instanceof BookmarksDocument)) {
				continue;
			}

			try {
				$this->updateDocumentFromBookmarksDocument($document);
			} catch (Exception $e) {
				$document->getIndex()
						 ->setStatus(Index::INDEX_IGNORE);
				echo 'Exception: ' . json_encode($e->getTrace()) . ' - ' . $e->getMessage() . "\n";
			}

			$index[] = $document;
		}

		return $index;
	}


	/**
	 * @param BookmarksDocument $document
	 *
	 * @throws WebpageIsNotIndexableException
	 */
	private function updateDocumentFromBookmarksDocument(BookmarksDocument $document) {
		$userId = $document->getAccess()
						   ->getOwnerId();

		$bookmark = $this->bookmarksClass->findUniqueBookmark($document->getId(), $userId);
		$document->addPart('description', $bookmark['description']);

		$html = $this->getWebpageFromUrl($document->getSource());
		$document->setContent(base64_encode($html), IndexDocument::ENCODED_BASE64);
	}


	/**
	 * @param $url
	 *
	 * @return mixed
	 * @throws WebpageIsNotIndexableException
	 */
	private function getWebpageFromUrl($url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

		$html = curl_exec($curl);
		if (curl_error($curl)) {
			throw new WebpageIsNotIndexableException('Webpage is not reachable');
		}

		curl_close($curl);

		return $html;
	}


	/**
	 * @param IndexDocument $document
	 *
	 * @return bool
	 */
	public function isDocumentUpToDate($document) {
		$index = $document->getIndex();

		if ($index->getStatus() !== Index::INDEX_OK) {
			return false;
		}

		$s = $this->configService->getAppValue(ConfigService::BOOKMARKS_TTL) * 3600 * 24;

		return ($index->getLastIndex() > time() - $s);
	}


	/**
	 * @param $bookmark
	 * @param $userId
	 *
	 * @return BookmarksDocument
	 */
	private function generateBookmarksDocumentFromBookmark($bookmark, $userId) {
		$document = new BookmarksDocument($bookmark['id']);

		$document->setAccess(new DocumentAccess($userId))
				 ->setModifiedTime($bookmark['lastmodified'])
				 ->setSource($bookmark['url'])
				 ->setTitle($bookmark['title'])
				 ->setTags($bookmark['tags']);

		return $document;
	}


	/**
	 * @param Index $index
	 *
	 * @return null|BookmarksDocument
	 */
	public function updateDocument(Index $index) {
		try {
			$document = $this->generateDocumentFromIndex($index);

			return $document;
		} catch (WebpageIsNotIndexableException $e) {
			return null;
		}
	}


	/**
	 * @param Index $index
	 *
	 * @return BookmarksDocument
	 * @throws WebpageIsNotIndexableException
	 */
	private function generateDocumentFromIndex(Index $index) {

		$bookmark =
			$this->bookmarksClass->findUniqueBookmark(
				$index->getDocumentId(), $index->getOwnerId()
			);

		if (sizeof($bookmark) === 0) {
			$index->setStatus(Index::INDEX_REMOVE);
			$document = new BookmarksDocument($index->getDocumentId());
			$document->setIndex($index);

			return $document;
		}

		$document = $this->generateBookmarksDocumentFromBookmark($bookmark, $index->getOwnerId());
		$document->setIndex($index);

		$this->updateDocumentFromBookmarksDocument($document);

		return $document;
	}


}