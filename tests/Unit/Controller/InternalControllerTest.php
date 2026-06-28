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
use OCA\Passman\Controller\InternalController;
use OCA\Passman\Db\Credential;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Tests\Unit\Support\AppConfigMockTrait;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(InternalController::class)]
class InternalControllerTest extends TestCase {
	use AppConfigMockTrait;

	private InternalController $controller;
	private CredentialService  $credentialService;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialService = $this->createMock(CredentialService::class);

		$this->controller = new InternalController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'john',
			$this->credentialService,
			$this->createMock(NotificationService::class),
			$this->createMock(IConfig::class),
			$this->createMock(IAppManager::class),
			$this->createAppConfigMock(),
		);
	}

	public function testRemind(): void {
		$this->credentialService->method('getCredentialById')->with(1, 'john')->willReturn(new Credential());
		$this->credentialService->method('updateCredentialEntity');
		$this->controller->remind(1);
		$this->addToAssertionCount(1);
	}

	public function testRead(): void {
		$this->credentialService->method('credentialExistsById')->with(1)->willReturn(false);
		$this->controller->read(1);
		$this->addToAssertionCount(1);
	}

	public function testGetSettings(): void {
		$result = $this->controller->getSettings();
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	public function testSaveSettings(): void {
		$this->controller->saveSettings('test', 'test');
		$this->addToAssertionCount(1);
	}
}
