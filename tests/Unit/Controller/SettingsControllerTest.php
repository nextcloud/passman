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

namespace OCA\Passman\Tests\Unit\Controller;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Controller\SettingsController;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Tests\Unit\Support\AppConfigMockTrait;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Config\IUserConfig;
use OCP\IL10N;
use OCP\IRequest;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Controller\SettingsController
 */
class SettingsControllerTest extends TestCase {
	use AppConfigMockTrait;

	private SettingsController $controller;

	protected function setUp(): void {
		parent::setUp();

		$appConfig = $this->createAppConfigMock();
		$userConfig = $this->createMock(IUserConfig::class);
		$settings = new SettingsService('admin', Application::APP_ID, $appConfig, $userConfig);

		$this->controller = new SettingsController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'admin',
			$settings,
			$this->createMock(IL10N::class),
		);
	}

	/** @covers ::getForm */
	public function testGetForm(): void {
		$result = $this->controller->getForm();
		$this->assertInstanceOf(TemplateResponse::class, $result);
	}

	/** @covers ::getSection */
	public function testGetSection(): void {
		$result = $this->controller->getSection();
		$this->assertIsString($result);
	}

	/** @covers ::getPriority */
	public function testGetPriority(): void {
		$result = $this->controller->getPriority();
		$this->assertIsNumeric($result);
	}

	/** @covers ::getSettings */
	public function testGetSettings(): void {
		$result = $this->controller->getsettings();
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	/** @covers ::saveUserSetting */
	public function testSaveUserSetting(): void {
		$result = $this->controller->saveUserSetting('test', 'value');
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	/** @covers ::saveAdminSetting */
	public function testSaveAdminSetting(): void {
		$result = $this->controller->saveAdminSetting('admin', 'value');
		$this->assertInstanceOf(JSONResponse::class, $result);
	}
}
