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
use OCA\Passman\Controller\VaultController;
use OCA\Passman\Db\Vault;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\DeleteVaultRequestService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Controller\VaultController
 */
class VaultControllerTest extends TestCase {
	private VaultController $controller;
	private VaultService $vaultService;

	protected function setUp(): void {
		parent::setUp();

		$this->vaultService = $this->createMock(VaultService::class);

		$this->controller = new VaultController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'example',
			$this->vaultService,
			$this->createMock(CredentialService::class),
			$this->createMock(DeleteVaultRequestService::class),
			$this->createMock(LoggerInterface::class),
		);
	}

	/** @covers ::listVaults */
	public function testListVaults(): void {
		$this->vaultService->method('getByUser')->willReturn([]);
		$result = $this->controller->listVaults();
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	/** @covers ::create */
	public function testCreate(): void {
		$this->vaultService->method('createVault')->willReturn(new Vault());
		$result = $this->controller->create('My test vault');
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	/** @covers ::get */
	public function testGet(): void {
		$this->vaultService->method('getByGuid')->willThrowException(new \RuntimeException('not found'));
		$result = $this->controller->get('');
		$this->assertInstanceOf(NotFoundJSONResponse::class, $result);
	}

	/** @covers ::update */
	public function testUpdate(): void {
		$this->vaultService->method('getByGuid')->willReturn(new Vault());
		$this->vaultService->method('updateVault');
		$this->controller->update('6AD30804-BFFC-4EFC-97F8-20A126FA1709', 'testname', null);
		$this->addToAssertionCount(1);
	}

	/** @covers ::updateSharingKeys */
	public function testUpdateSharingKeys(): void {
		$this->vaultService->method('getByGuid')->willThrowException(new \RuntimeException('not found'));
		$this->controller->updateSharingKeys('6AD30804-BFFC-4EFC-97F8-20A126FA1709', null, null);
		$this->addToAssertionCount(1);
	}

	/** @covers ::delete */
	public function testDelete(): void {
		$this->vaultService->method('getByGuid')->willThrowException(new \RuntimeException('not found'));
		$result = $this->controller->delete('');
		$this->assertInstanceOf(NotFoundJSONResponse::class, $result);
	}
}
