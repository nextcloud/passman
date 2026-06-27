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

namespace OCA\Passman\Tests\Unit\Lib\Service;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Utility\PermissionEntity;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Service\ShareService
 */
class ShareServiceTest extends TestCase {
	private SharingACLMapper&MockObject $sharingACLMapper;
	private ShareRequestMapper&MockObject $shareRequestMapper;
	private CredentialMapper&MockObject $credentialMapper;
	private CredentialRevisionService&MockObject $revisionService;
	private EncryptService&MockObject $encryptService;
	private IManager&MockObject $notificationManager;
	private ShareService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->sharingACLMapper = $this->createMock(SharingACLMapper::class);
		$this->shareRequestMapper = $this->createMock(ShareRequestMapper::class);
		$this->credentialMapper = $this->createMock(CredentialMapper::class);
		$this->revisionService = $this->createMock(CredentialRevisionService::class);
		$this->encryptService = $this->createMock(EncryptService::class);
		$this->notificationManager = $this->createMock(IManager::class);

		$this->service = new ShareService(
			$this->sharingACLMapper,
			$this->shareRequestMapper,
			$this->credentialMapper,
			$this->revisionService,
			$this->encryptService,
			$this->notificationManager,
		);
	}

	/** @covers ::createBulkRequests */
	public function testCreateBulkRequests(): void {
		$this->shareRequestMapper->expects($this->exactly(2))
			->method('createRequest')
			->willReturnCallback(static function (ShareRequest $request): ShareRequest {
				$request->setId(1);
				return $request;
			});

		$requests = $this->service->createBulkRequests(
			10,
			'item-guid',
			[
				['user_id' => 'alice', 'vault_id' => 1, 'guid' => 'vault-a', 'key' => 'key-a'],
				['user_id' => 'bob', 'vault_id' => 2, 'guid' => 'vault-b', 'key' => 'key-b'],
			],
			PermissionEntity::READ,
			'owner',
		);

		$this->assertCount(2, $requests);
		$this->assertSame('alice', $requests[0]->getTargetUserId());
		$this->assertSame('bob', $requests[1]->getTargetUserId());
		$this->assertSame('owner', $requests[0]->getFromUserId());
		$this->assertSame(PermissionEntity::READ, $requests[0]->getPermissions());
	}

	/** @covers ::createACLEntry */
	public function testCreateACLEntrySetsCreatedTimestamp(): void {
		$acl = new SharingACL();
		$this->sharingACLMapper->expects($this->once())
			->method('createACLEntry')
			->with($this->callback(static function (SharingACL $entry): bool {
				return $entry->getCreated() !== null && $entry->getCreated() > 0;
			}))
			->willReturnArgument(0);

		$result = $this->service->createACLEntry($acl);

		$this->assertSame($acl, $result);
		$this->assertGreaterThan(0, $result->getCreated());
	}

	/** @covers ::applyShare */
	public function testApplyShareCreatesAclAndCleansPendingRequests(): void {
		$request = new ShareRequest();
		$request->setItemId(5);
		$request->setItemGuid('item-guid');
		$request->setTargetUserId('alice');
		$request->setTargetVaultId(3);
		$request->setTargetVaultGuid('vault-guid');
		$request->setPermissions(PermissionEntity::READ | PermissionEntity::WRITE);
		$request->setCreated(1234);

		$this->shareRequestMapper->expects($this->once())
			->method('getRequestByItemAndVaultGuid')
			->with('item-guid', 'vault-guid')
			->willReturn($request);
		$this->sharingACLMapper->expects($this->once())
			->method('createACLEntry')
			->with($this->callback(static function (SharingACL $acl): bool {
				return $acl->getUserId() === 'alice'
					&& $acl->getSharedKey() === 'final-key'
					&& $acl->getPermissions() === (PermissionEntity::READ | PermissionEntity::WRITE);
			}))
			->willReturnArgument(0);
		$this->shareRequestMapper->expects($this->once())
			->method('cleanItemRequestsForUser')
			->with(5, 'alice');

		$this->service->applyShare('item-guid', 'vault-guid', 'final-key');
	}

	/** @covers ::getItemHistory */
	public function testGetItemHistoryReturnsEmptyWithoutHistoryPermission(): void {
		$acl = new SharingACL();
		$acl->setPermissions(PermissionEntity::READ);
		$acl->setItemGuid('item-guid');

		$this->sharingACLMapper->expects($this->once())
			->method('getItemACL')
			->with('alice', 'item-guid')
			->willReturn($acl);
		$this->revisionService->expects($this->never())
			->method('getRevisions');

		$this->assertSame([], $this->service->getItemHistory('alice', 'item-guid'));
	}

	/** @covers ::unshareCredential */
	public function testUnshareCredentialDeletesAclAndRequestsAndMarksNotifications(): void {
		$acl = new SharingACL();
		$acl->setId(1);
		$request = new ShareRequest();
		$request->setId(9);
		$request->setTargetUserId('alice');

		$this->sharingACLMapper->expects($this->once())
			->method('getCredentialAclList')
			->with('item-guid')
			->willReturn([$acl]);
		$this->shareRequestMapper->expects($this->once())
			->method('getShareRequestsByItemGuid')
			->with('item-guid')
			->willReturn([$request]);
		$this->sharingACLMapper->expects($this->once())
			->method('deleteShareACL')
			->with($acl);
		$this->shareRequestMapper->expects($this->once())
			->method('deleteShareRequest')
			->with($request);

		$notification = $this->createMock(INotification::class);
		$this->notificationManager->expects($this->once())
			->method('createNotification')
			->willReturn($notification);
		$notification->expects($this->once())
			->method('setApp')
			->with(Application::APP_ID)
			->willReturnSelf();
		$notification->expects($this->once())
			->method('setObject')
			->with('passman_share_request', 9)
			->willReturnSelf();
		$notification->expects($this->once())
			->method('setUser')
			->with('alice')
			->willReturnSelf();
		$this->notificationManager->expects($this->once())
			->method('markProcessed')
			->with($notification);

		$this->service->unshareCredential('item-guid');
	}
}
