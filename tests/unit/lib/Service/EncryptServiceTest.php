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

use OCA\Passman\Service\EncryptService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use PHPUnit_Framework_TestCase;
use OCA\Passman\Service\SettingsService;

/**
 * Class SettingsServiceTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Service\EncryptService
 */
class EncryptServiceTest extends PHPUnit_Framework_TestCase {

	private $service;
	private $testKey;

	public function setUp() {
		$config = $this->getMockBuilder('OCP\IConfig')->getMock();
		$userId = 'admin';
		$settings_service = new SettingsService($userId, $config, 'passman');
		$this->service = new EncryptService($settings_service);

	}

	/**
	 * @covers ::makeKey
	 */
	public function testMakeKey() {
		$this->testKey = $this->service->makeKey('userKey', 'serverKey', 'userSuppliedKey');
		$this->assertTrue($this->testKey === '967efb38599fb81ebc95b280e7c86cda0593e469f6a391caf9e9fee7c3976fd1edcdeefdb6a99e9f0bc47fda4b77fb8309c1955211dccf1dab1aad00c2ad5656');
	}
}