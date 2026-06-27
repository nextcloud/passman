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

namespace OCA\Passman\Tests\Unit\Lib\Service;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Tests\Unit\Support\AppConfigMockTrait;
use OCP\Config\IUserConfig;
use OCP\IConfig;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Service\EncryptService
 */
class EncryptServiceTest extends TestCase {
	use AppConfigMockTrait;

	private EncryptService $service;

	protected function setUp(): void {
		parent::setUp();

		$appConfig = $this->createAppConfigMock();
		$userConfig = $this->createMock(IUserConfig::class);
		$settingsService = new SettingsService('admin', Application::APP_ID, $appConfig, $userConfig);

		$config = $this->createMock(IConfig::class);
		$config->method('getSystemValue')->willReturn('');

		$this->service = new EncryptService($settingsService, $config);
	}

	/** @covers ::makeKey */
	public function testMakeKey(): void {
		$key = $this->service->makeKey('userKey', 'serverKey', 'userSuppliedKey');
		$this->assertSame(
			'967efb38599fb81ebc95b280e7c86cda0593e469f6a391caf9e9fee7c3976fd1edcdeefdb6a99e9f0bc47fda4b77fb8309c1955211dccf1dab1aad00c2ad5656',
			$key,
		);
	}
}
