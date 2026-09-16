<?php
/**
 * Nextcloud - Passman
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
use OCA\Passman\Controller\IconController;
use OCA\Passman\Db\Credential;
use OCA\Passman\Service\CredentialService;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\DB\Exception as DbException;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(IconController::class)]
class IconControllerTest extends TestCase {
	private CredentialService&MockObject $credentialService;
	private IconController $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialService = $this->createMock(CredentialService::class);
		$this->controller = new IconController(
			Application::APP_ID,
			$this->createStub(IRequest::class),
			'example',
			$this->credentialService,
			$this->createStub(IAppManager::class),
			$this->createStub(IURLGenerator::class),
		);
	}

	public function testGetIconReturnsDownloadWhenUpdateThrowsDbException(): void {
		$credential = new Credential();
		$credential->setUserId('example');
		$credential->setIcon('{}');

		$this->credentialService->method('getCredentialById')
			->with(42, 'example')
			->willReturn($credential);
		$this->credentialService->expects($this->once())
			->method('updateCredential')
			->willThrowException(new DbException('oversized icon'));

		$result = $this->controller->getIcon(base64_encode('http://127.0.0.1:1/'), 42);

		$this->assertInstanceOf(DataDownloadResponse::class, $result);
	}
}
