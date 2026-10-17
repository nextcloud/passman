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
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(InternalController::class)]
class InternalControllerTest extends TestCase {
	use AppConfigMockTrait;

	private InternalController             $controller;
	private CredentialService&MockObject   $credentialService;
	private NotificationService&MockObject $notificationService;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialService = $this->createMock(CredentialService::class);
		$this->notificationService = $this->createMock(NotificationService::class);

		$this->controller = new InternalController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'john',
			$this->credentialService,
			$this->notificationService,
			$this->createMock(IConfig::class),
			$this->createMock(IAppManager::class),
			$this->createAppConfigMock(),
		);
	}

	public function testRemindPostponesExpiryOfOwnCredential(): void {
		$credential = new Credential();
		$this->credentialService->expects($this->once())
			->method('getOwnedCredentialById')->with(1, 'john')->willReturn($credential);
		$this->credentialService->expects($this->once())
			->method('updateCredentialEntity')
			->with($this->callback(static fn(Credential $c): bool => $c === $credential
				&& abs($c->getExpireTime() - (time() + 24 * 60 * 60)) <= 5))
			->willReturnArgument(0);
		$this->notificationService->expects($this->once())
			->method('markNotificationOfCredentialAsProcessed')->with(1, 'john');

		$this->controller->remind('1');
	}

	public function testReadClearsExpiryOfOwnCredential(): void {
		$credential = new Credential();
		$credential->setExpireTime(12345);
		$this->credentialService->expects($this->once())
			->method('getOwnedCredentialById')->with(1, 'john')->willReturn($credential);
		$this->credentialService->expects($this->once())
			->method('updateCredentialEntity')
			->with($this->callback(static fn(Credential $c): bool => $c === $credential && $c->getExpireTime() === 0))
			->willReturnArgument(0);
		$this->notificationService->expects($this->once())
			->method('markNotificationOfCredentialAsProcessed')->with(1, 'john');

		$this->controller->read('1');
	}

	public static function notificationActionProvider(): array {
		return [
			'remind' => ['remind'],
			'read'   => ['read'],
		];
	}

	/**
	 * A user a credential is shared with (or any other user) must not change the owner's expire time.
	 * The owner-only lookup throws for foreign and missing credentials alike, so both cases behave identically
	 * (no existence oracle): nothing is updated, only the caller's own notification for that id is dismissed.
	 */
	#[DataProvider('notificationActionProvider')]
	public function testForeignOrMissingCredentialIsNotModified(string $action): void {
		$this->credentialService->expects($this->once())
			->method('getOwnedCredentialById')->with(1, 'john')
			->willThrowException(new DoesNotExistException(''));
		// the share-aware lookup (which grants access on READ permission) must not be used
		$this->credentialService->expects($this->never())->method('getCredentialById');
		$this->credentialService->expects($this->never())->method('updateCredentialEntity');
		$this->notificationService->expects($this->once())
			->method('markNotificationOfCredentialAsProcessed')->with(1, 'john');

		$this->assertNull($this->controller->$action('1'));
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
