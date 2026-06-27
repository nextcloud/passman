<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

declare(strict_types=1);

namespace OCA\Passman\Tests\Unit\Search;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Search\Provider;
use OCA\Passman\Service\SettingsService;
use OCP\IL10N;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Search\Provider
 */
class ProviderTest extends TestCase {
	private Provider $provider;

	protected function setUp(): void {
		parent::setUp();

		$l10n = $this->createMock(IL10N::class);
		$l10n->method('t')->willReturnArgument(0);

		$settings = $this->createMock(SettingsService::class);
		$settings->method('getAppSetting')->willReturn(0);

		$this->provider = new Provider(
			$l10n,
			$this->createMock(IURLGenerator::class),
			$this->createMock(IDBConnection::class),
			$settings,
		);
	}

	public function testGetId(): void {
		$this->assertSame(Application::APP_ID, $this->provider->getId());
	}

	public function testGetName(): void {
		$this->assertSame(Application::APP_NAME, $this->provider->getName());
	}

	public function testGetOrderInAppRoute(): void {
		$this->assertSame(-1, $this->provider->getOrder('passman.Page.index', []));
	}

	public function testGetOrderOutsideApp(): void {
		$this->assertSame(25, $this->provider->getOrder('files.view.index', []));
	}

	public function testSearchWhenDisabled(): void {
		$user = $this->createMock(\OCP\IUser::class);
		$query = $this->createMock(ISearchQuery::class);

		$result = $this->provider->search($user, $query);

		$this->assertInstanceOf(SearchResult::class, $result);
	}
}
