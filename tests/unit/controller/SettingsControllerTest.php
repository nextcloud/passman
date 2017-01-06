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
 * Class SettingsControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Controller\SettingsController
 */
class SettingsControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$IL10N = $this->getMockBuilder('OCP\IL10N')->getMock();
		$config = $this->getMockBuilder('OCP\IConfig')->getMock();
		$userId = 'admin';
		$settings = new SettingsService($userId, $config, 'passman');

		$this->controller = new SettingsController(
			'passman', $request, $userId, $settings, $IL10N
		);
	}

	/**
	 * @covers ::getForm
	 */
	public function testGetForm() {
		$result = $this->controller->getForm();
		$this->assertTrue($result instanceof TemplateResponse);
	}
	/**
	 * @covers ::getSection
	 */
	public function testGetSection() {
		$result = $this->controller->getSection();
		$this->assertTrue(is_string($result));
	}

	/**
	 * @covers ::getPriority
	 */
	public function testGetPriority() {
		$result = $this->controller->getPriority();
		$this->assertTrue(is_numeric($result));
	}

	/**
	 * @covers ::getSettings
	 */
	public function testGetSettings() {
		$result = $this->controller->getsettings();
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::saveUserSetting
	 */
	public function testSaveUserSetting() {
		$result = $this->controller->saveUserSetting('test','value');
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::saveAdminSetting
	 */
	public function testSaveAdminSetting() {
		$result = $this->controller->saveAdminSetting('admin', 'value');
		$this->assertTrue($result instanceof JSONResponse);
	}
}