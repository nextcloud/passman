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
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Tests\Unit\Support\AppConfigMockTrait;
use OCP\Config\IUserConfig;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Service\SettingsService
 */
class SettingsServiceTest extends TestCase {
	use AppConfigMockTrait;

	private SettingsService $service;

	protected function setUp(): void {
		parent::setUp();

		$appConfig = $this->createAppConfigMock(static function (string $app, string $key, mixed $default): mixed {
			if ($key === 'link_sharing_enabled') {
				return '0';
			}
			return $default;
		});

		$userConfig = $this->createMock(IUserConfig::class);

		$this->service = new SettingsService('admin', Application::APP_ID, $appConfig, $userConfig);
	}

	/** @covers ::getAppSettings */
	public function testGetAppSettings(): void {
		$result = $this->service->getAppSettings();
		$this->assertIsArray($result);
	}

	/** @covers ::getAppSetting */
	public function testGetAppSetting(): void {
		$result = $this->service->getAppSetting('settings_loaded', 1);
		$this->assertSame(1, $result);
	}

	/** @covers ::setAppSetting */
	public function testSetAppSetting(): void {
		$this->service->setAppSetting('settings_loaded', 0);
		$this->assertSame(0, $this->service->getAppSetting('settings_loaded'));
	}

	/** @covers ::setUserSetting */
	public function testSetUserSetting(): void {
		$this->service->setUserSetting('test', 'value');
		$this->addToAssertionCount(1);
	}

	/** @covers ::isEnabled */
	public function testIsEnabled(): void {
		$result = $this->service->isEnabled('link_sharing_enabled');
		$this->assertFalse($result);
	}
}
