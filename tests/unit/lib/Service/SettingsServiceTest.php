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

namespace OCA\Passman\Controller;

use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use PHPUnit_Framework_TestCase;
use OCA\Passman\Service\SettingsService;

/**
 * Class SettingsServiceTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Service\SettingsService
 */
class SettingsServiceTest extends PHPUnit_Framework_TestCase {

	private $service;

	public function setUp() {
		$config = $this->getMockBuilder('OCP\IConfig')->getMock();
		$userId = 'admin';
		$this->service = new SettingsService($userId, $config, 'passman');
	}

	/**
	 * @covers ::getAppSettings
	 */
	public function testGetAppSettings() {
		$result = $this->service->getAppSettings();
		$this->assertTrue(is_array($result));
	}

	/**
	 * @covers ::getAppSetting
	 */
	public function testGetAppSetting() {
		$result = $this->service->getAppSetting('settings_loaded', 1);
		$this->assertTrue($result === 1);
	}

	/**
	 * @covers ::setAppSetting
	 */
	public function testSetAppSetting() {
		$this->service->setAppSetting('settings_loaded', 0);
		$this->assertTrue( $this->service->getAppSetting('settings_loaded') === 0);
	}
	/**
	 * @covers ::setUserSetting
	 */
	public function testSetUserSetting() {
		$this->service->setUserSetting('test','value');
	}

	/**
	 * On tests link_sharing is disabled
	 * @covers ::isEnabled
	 */
	public function testIsEnabled() {
		$result = $this->service->isEnabled('link_sharing_enabled');
		$this->assertFalse($result);
	}
}