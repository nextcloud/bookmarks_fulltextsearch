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


namespace OCA\Bookmarks_FullTextSearch\AppInfo;


use Exception;
use OCA\Bookmarks_FullTextSearch\Provider\BookmarksProvider;
use OCP\App\IAppManager;
use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;
use OCP\FullTextSearch\IFullTextSearchManager;
use OCP\FullTextSearch\Model\IIndex;
use OCP\IUserSession;
use OCP\Util;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;


class Application extends App {


	const APP_NAME = 'bookmarks_fulltextsearch';


	/** @var IFullTextSearchManager */
	private $fullTextSearchManager;

	/** @var EventDispatcherInterface */
	private $eventDispatcher;


	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		parent::__construct(self::APP_NAME, $params);

		$c = $this->getContainer();

		try {
			$this->fullTextSearchManager = $c->query(IFullTextSearchManager::class);
		} catch (QueryException $e) {
		}
	}


	/**
	 * Register Hooks
	 */
	public function registerHooks() {
		$this->eventDispatcher = \OC::$server->getEventDispatcher();

		$this->registerHookCreate();
		$this->registerHookUpdate();
		$this->registerHookDelete();
	}


	/**
	 *
	 */
	private function registerHookCreate() {
		$this->eventDispatcher->addListener(
			'\OCA\Bookmarks::onBookmarkCreate', function(GenericEvent $e) {

			$this->fullTextSearchManager->createIndex(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID,
				(string)$e->getArgument('id'),
				$e->getArgument('userId')
			);
			$this->fullTextSearchManager->updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID,
				(string)$e->getArgument('id'),
				IIndex::INDEX_FULL
			);
		}
		);
	}


	/**
	 *
	 */
	private function registerHookUpdate() {
		$this->eventDispatcher->addListener(
			'\OCA\Bookmarks::onBookmarkUpdate', function(GenericEvent $e) {

			$this->fullTextSearchManager->updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID, (string)$e->getArgument('id'),
				IIndex::INDEX_FULL
			);
		}
		);
	}


	/**
	 *
	 */
	private function registerHookDelete() {
		$this->eventDispatcher->addListener(
			'\OCA\Bookmarks::onBookmarkDelete', function(GenericEvent $e) {

			$this->fullTextSearchManager->updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID, (string)$e->getArgument('id'),
				IIndex::INDEX_REMOVE
			);
		}
		);
	}


	/**
	 *
	 * @throws QueryException
	 */
	public function registerBookmarksSearch() {
		$container = $this->getContainer();

		/** @var IUserSession $userSession */
		$userSession = $container->query(IUserSession::class);

		if (!$userSession->isLoggedIn()) {
			return;
		}

		$user = $userSession->getUser();

		try {
			$appManager = $container->query(IAppManager::class);
			if ($appManager->isEnabledForUser('fulltextsearch', $user)) {
				Util::addStyle(self::APP_NAME, 'fulltextsearch');
				$this->includeFullTextSearch();
			}
		} catch (Exception $e) {
		}
	}


	/**
	 *
	 */
	private function includeFullTextSearch() {
		$this->eventDispatcher->addListener(
			'\OCA\Bookmarks::loadAdditionalScripts', function() {
			if ($this->fullTextSearchManager->isProviderIndexed(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID
			)) {
				$this->fullTextSearchManager->addJavascriptAPI();
				Util::addScript(Application::APP_NAME, 'bookmarks');
			}
		}
		);
	}

}

