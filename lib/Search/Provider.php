<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

namespace OCA\Passman\Search;

use OCA\Passman\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

class Provider implements IProvider {

	/** @var IL10N */
	private IL10N $l10n;

	/** @var IURLGenerator */
	private IURLGenerator $urlGenerator;

	public function __construct(IL10N $l10n, IURLGenerator $urlGenerator) {
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10n->t('Passman');
	}

	public function getOrder(string $route, array $routeParameters): int {
		if (strpos($route, Application::APP_ID . '.') === 0) {
			// Active app, prefer my results
			return -1;
		}

		return 25;
	}

	public function search(IUser $user, ISearchQuery $query): SearchResult {
		return SearchResult::complete(
			$this->l10n->t(Application::APP_ID),
			[
				new SearchResultEntry(
					$this->urlGenerator->imagePath(Application::APP_ID, 'app.svg'),
					$this->l10n->t('Search in current page'),
					$this->l10n->t('This requires an already unlocked Passman vault'),
					'#?search=' . $query->getTerm()
				)
			]
		);
	}
}
