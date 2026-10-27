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
use OCA\Passman\Service\IconService;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\DB\Exception as DbException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(IconController::class)]
class IconControllerTest extends TestCase {
	// 1x1 transparent PNG
	private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

	private CredentialService&MockObject $credentialService;
	private IClient&MockObject $client;
	private IconController $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialService = $this->createMock(CredentialService::class);
		$this->client = $this->createMock(IClient::class);
		$clientService = $this->createStub(IClientService::class);
		$clientService->method('newClient')->willReturn($this->client);

		$this->controller = new IconController(
			Application::APP_ID,
			$this->createStub(IRequest::class),
			'example',
			$this->credentialService,
			$this->createStub(IAppManager::class),
			$this->createStub(IURLGenerator::class),
			$clientService,
		);
	}

	private function response(int $status, string $body, string $contentType): IResponse {
		$response = $this->createStub(IResponse::class);
		$response->method('getStatusCode')->willReturn($status);
		$response->method('getBody')->willReturn($body);
		$response->method('getHeader')->willReturn($contentType);
		return $response;
	}

	public function testGetSingleIconReturnsNothingForBlockedLocalAddress(): void {
		// The request must go through the Nextcloud client, which refuses local/private targets
		$this->client->expects($this->atLeastOnce())
			->method('get')
			->willThrowException(new LocalServerException('local'));

		$result = $this->controller->getSingleIcon(base64_encode('http://169.254.169.254/latest/meta-data/'));

		$this->assertInstanceOf(JSONResponse::class, $result);
		$this->assertSame([], $result->getData());
	}

	public function testGetSingleIconDoesNotReturnNonImageResponses(): void {
		// e.g. allow_local_remote_servers enabled: the internal response must still not be exfiltrated
		$this->client->method('get')->willReturn($this->response(200, '{"secret":"value"}', 'image/x-icon'));

		$result = $this->controller->getSingleIcon(base64_encode('http://10.0.0.1/internal-api'));

		$this->assertInstanceOf(JSONResponse::class, $result);
		$this->assertSame([], $result->getData());
	}

	public function testGetSingleIconRejectsNonHttpSchemeWithoutRequest(): void {
		$this->client->expects($this->never())->method('get');

		$result = $this->controller->getSingleIcon(base64_encode('ftp://internal.example/favicon.ico'));

		$this->assertInstanceOf(JSONResponse::class, $result);
		$this->assertSame([], $result->getData());
	}

	public function testGetSingleIconReturnsImage(): void {
		$png = base64_decode(self::PNG);
		$this->client->method('get')->willReturnCallback(
			fn(string $url): IResponse => $url === 'https://example.com/favicon.ico'
				? $this->response(200, $png, 'image/png')
				: $this->response(200, '<html><head></head><body></body></html>', 'text/html')
		);

		$result = $this->controller->getSingleIcon(base64_encode('https://example.com/'));

		$this->assertInstanceOf(JSONResponse::class, $result);
		$this->assertSame(base64_encode($png), $result->getData()['content']);
	}

	public function testGetIconReturnsDefaultIconForBlockedLocalAddress(): void {
		$this->client->method('get')->willThrowException(new LocalServerException('local'));

		$result = $this->controller->getIcon(base64_encode('http://127.0.0.1:6379/'), null);

		$this->assertInstanceOf(DataDownloadResponse::class, $result);
		// the built-in fallback icon, not anything fetched from the target
		$this->assertTrue(IconService::isImage($result->render()));
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

		$this->client->method('get')->willThrowException(new LocalServerException('local'));

		$result = $this->controller->getIcon(base64_encode('http://127.0.0.1:1/'), 42);

		$this->assertInstanceOf(DataDownloadResponse::class, $result);
	}
}
