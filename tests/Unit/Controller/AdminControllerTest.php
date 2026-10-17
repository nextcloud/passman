<?php
/**
 * Nextcloud - passman
 *
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
use OCA\Passman\Controller\AdminController;
use OCA\Passman\Db\DeleteVaultRequest;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\DeleteVaultRequestService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\VaultService;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(AdminController::class)]
class AdminControllerTest extends TestCase {
	private const VAULT_GUID = '6AD30804-BFFC-4EFC-97F8-20A126FA1709';

	private AdminController                      $controller;
	private DeleteVaultRequestService&MockObject $deleteVaultRequestService;
	private IGroupManager&MockObject             $groupManager;

	protected function setUp(): void {
		parent::setUp();

		$this->deleteVaultRequestService = $this->createMock(DeleteVaultRequestService::class);
		$this->groupManager = $this->createMock(IGroupManager::class);

		$this->controller = new AdminController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'john',
			$this->createMock(VaultService::class),
			$this->createMock(CredentialService::class),
			$this->createMock(FileService::class),
			$this->createMock(CredentialRevisionService::class),
			$this->deleteVaultRequestService,
			$this->createMock(IUserManager::class),
			$this->groupManager,
			$this->createMock(IL10N::class),
		);
	}

	private function pendingRequestBy(string $userId): DeleteVaultRequest {
		$request = new DeleteVaultRequest();
		$request->setVaultGuid(self::VAULT_GUID);
		$request->setRequestedBy($userId);
		$this->deleteVaultRequestService->method('getDeleteRequestForVault')
			->with(self::VAULT_GUID)
			->willReturn($request);
		return $request;
	}

	public function testRequesterCanCancelOwnDeletionRequest(): void {
		$request = $this->pendingRequestBy('john');
		$this->deleteVaultRequestService->expects($this->once())
			->method('removeDeleteRequestForVault')
			->with($request);

		$response = $this->controller->deleteRequestDeletion(self::VAULT_GUID);

		$this->assertSame(['result' => true], $response->getData());
	}

	public function testAdminCanIgnoreDeletionRequestOfOtherUser(): void {
		// The admin settings page uses this route to ignore a request
		$request = $this->pendingRequestBy('jane');
		$this->groupManager->method('isAdmin')->with('john')->willReturn(true);
		$this->deleteVaultRequestService->expects($this->once())
			->method('removeDeleteRequestForVault')
			->with($request);

		$response = $this->controller->deleteRequestDeletion(self::VAULT_GUID);

		$this->assertSame(['result' => true], $response->getData());
	}

	public function testUserCannotCancelDeletionRequestOfOtherUser(): void {
		$this->pendingRequestBy('jane');
		$this->groupManager->method('isAdmin')->with('john')->willReturn(false);
		$this->deleteVaultRequestService->expects($this->never())->method('removeDeleteRequestForVault');

		$response = $this->controller->deleteRequestDeletion(self::VAULT_GUID);

		// Same response as for a missing request, so other users' requests can't be probed
		$this->assertSame(['result' => false], $response->getData());
	}

	public function testMissingDeletionRequest(): void {
		$this->deleteVaultRequestService->method('getDeleteRequestForVault')->willReturn(false);
		$this->deleteVaultRequestService->expects($this->never())->method('removeDeleteRequestForVault');

		$response = $this->controller->deleteRequestDeletion(self::VAULT_GUID);

		$this->assertSame(['result' => false], $response->getData());
	}
}
