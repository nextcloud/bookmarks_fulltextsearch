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

namespace OCA\Bookmarks_FullTextSearch\AppInfo;

use OCA\Bookmarks_FullTextSearch\Provider\BookmarksProvider;
use OCA\FullTextSearch\Api\v1\FullTextSearch;
use OCA\FullTextSearch\Model\Index;
use OCP\App\IAppManager;
use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;
use OCP\IUserSession;
use OCP\Util;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {

	const APP_NAME = 'bookmarks_fulltextsearch';

	/** @var EventDispatcherInterface */
	private $eventDispatcher;


	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		parent::__construct(self::APP_NAME, $params);
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

			FullTextSearch::createIndex(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID,
				$e->getArgument('id'),
				$e->getArgument('userId')
			);
			FullTextSearch::updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID,
				$e->getArgument('id'),
				Index::INDEX_FULL
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

			FullTextSearch::updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID, $e->getArgument('id'),
				Index::INDEX_FULL
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

			FullTextSearch::updateIndexStatus(
				BookmarksProvider::BOOKMARKS_PROVIDER_ID, $e->getArgument('id'),
				Index::INDEX_REMOVE
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

		if ($container->query(IAppManager::class)
					  ->isEnabledForUser('fulltextsearch', $user)
			&& (FullTextSearch::isProviderIndexed(BookmarksProvider::BOOKMARKS_PROVIDER_ID))) {
			$this->includeFullTextSearch();
		}
	}


	/**
	 *
	 */
	private function includeFullTextSearch() {
		$this->eventDispatcher->addListener(
			'\OCA\Bookmarks::loadAdditionalScripts', function() {
			FullTextSearch::addJavascriptAPI();
			Util::addScript(Application::APP_NAME, 'bookmarks');
		}
		);
	}


}

