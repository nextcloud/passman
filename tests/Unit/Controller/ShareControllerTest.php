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
use OCA\Passman\Controller\ShareController;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\File;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCA\Passman\Utility\PermissionEntity;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(ShareController::class)]
class ShareControllerTest extends TestCase {
	private const USER = 'alice';

	private ShareService&MockObject        $shareService;
	private CredentialService&MockObject   $credentialService;
	private ActivityService&MockObject     $activityService;
	private NotificationService&MockObject $notificationService;
	private FileService&MockObject         $fileService;
	private ShareController                $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->shareService = $this->createMock(ShareService::class);
		$this->credentialService = $this->createMock(CredentialService::class);
		$this->activityService = $this->createMock(ActivityService::class);
		$this->notificationService = $this->createMock(NotificationService::class);
		$this->fileService = $this->createMock(FileService::class);

		// the controller is registered manually in Application::register() and gets the current IUser injected
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn(self::USER);
		$user->method('getDisplayName')->willReturn('Alice');

		$this->controller = new ShareController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$user,
			$this->createMock(IUserManager::class),
			$this->activityService,
			$this->createMock(VaultService::class),
			$this->shareService,
			$this->credentialService,
			$this->notificationService,
			$this->fileService,
			$this->createMock(IManager::class),
		);
	}

	private function credential(int $id = 5, string $guid = 'item-guid', string $owner = self::USER): Credential {
		$credential = new Credential();
		$credential->setId($id);
		$credential->setGuid($guid);
		$credential->setUserId($owner);
		$credential->setLabel('label');
		return $credential;
	}

	private function shareRequest(string $targetUser): ShareRequest {
		$request = new ShareRequest();
		$request->setId(9);
		$request->setItemId(5);
		$request->setItemGuid('item-guid');
		$request->setTargetUserId($targetUser);
		$request->setFromUserId('bob');
		return $request;
	}

	private function expectOwnedCredential(?Credential $credential = null): void {
		$this->credentialService->expects($this->once())
			->method('getCredentialByGUID')
			->with('item-guid', self::USER)
			->willReturn($credential ?? $this->credential());
	}

	private function expectForeignCredential(): void {
		// the owner restricted lookup does not find credentials of other users
		$this->credentialService->expects($this->once())
			->method('getCredentialByGUID')
			->with('item-guid', self::USER)
			->willThrowException(new DoesNotExistException(''));
	}

	public function testCreatePublicShareRejectsForeignCredential(): void {
		$this->expectForeignCredential();
		$this->shareService->expects($this->never())->method('createACLEntry');
		$this->shareService->expects($this->never())->method('updateCredentialACL');

		$response = $this->controller->createPublicShare(5, 'item-guid', PermissionEntity::READ, 0, 5);

		$this->assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testCreatePublicShareRejectsMismatchingItemId(): void {
		$this->expectOwnedCredential();
		$this->shareService->expects($this->never())->method('getACL');
		$this->shareService->expects($this->never())->method('createACLEntry');

		$response = $this->controller->createPublicShare(6, 'item-guid', PermissionEntity::READ, 0, 5);

		$this->assertInstanceOf(NotFoundResponse::class, $response);
	}

	public static function invalidPublicShareParameters(): array {
		return [
			'unlimited views'     => [PermissionEntity::READ, 0, -1],
			'zero views'          => [PermissionEntity::READ, 0, 0],
			'empty views'         => [PermissionEntity::READ, 0, ''],
			'missing views'       => [PermissionEntity::READ, 0, null],
			'non numeric views'   => [PermissionEntity::READ, 0, 'abc'],
			'too many views'      => [PermissionEntity::READ, 0, '1e30'],
			'negative expire'     => [PermissionEntity::READ, -1, 5],
			'non numeric expire'  => [PermissionEntity::READ, 'tomorrow', 5],
			'write permission'    => [PermissionEntity::READ | PermissionEntity::WRITE, 0, 5],
			'history permission'  => [PermissionEntity::READ | PermissionEntity::HISTORY, 0, 5],
			'owner permission'    => [PermissionEntity::READ | PermissionEntity::OWNER, 0, 5],
			'files without read'  => [PermissionEntity::FILES, 0, 5],
			'no permission'       => [0, 0, 5],
			'negative permission' => [-1, 0, 5],
		];
	}

	#[DataProvider('invalidPublicShareParameters')]
	public function testCreatePublicShareRejectsInvalidParameters(mixed $permissions, mixed $expireTimestamp, mixed $expireViews): void {
		$this->expectOwnedCredential();
		$this->shareService->expects($this->never())->method('createACLEntry');
		$this->shareService->expects($this->never())->method('updateCredentialACL');

		$response = $this->controller->createPublicShare(5, 'item-guid', $permissions, $expireTimestamp, $expireViews);

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testCreatePublicShareCreatesAclFromOwnedCredential(): void {
		$this->expectOwnedCredential();
		$this->shareService->method('getACL')
			->with(null, 'item-guid')
			->willThrowException(new DoesNotExistException(''));
		$this->shareService->expects($this->once())
			->method('createACLEntry')
			->with($this->callback(static fn(SharingACL $acl): bool => $acl->getItemId() === 5
				&& $acl->getItemGuid() === 'item-guid'
				&& $acl->getUserId() === null
				&& $acl->getPermissions() === (PermissionEntity::READ | PermissionEntity::FILES)
				&& $acl->getExpire() === 1760000000
				&& $acl->getExpireViews() === 5))
			->willReturnArgument(0);
		$this->activityService->expects($this->once())->method('add');

		// the frontend sends the expire timestamp as float (Date.getTime() / 1000)
		$this->controller->createPublicShare('5', 'item-guid', PermissionEntity::READ | PermissionEntity::FILES, 1760000000.123, '5');
	}

	public function testCreatePublicShareUpdatesExistingAcl(): void {
		$existing = new SharingACL();
		$existing->setId(3);
		$existing->setItemId(5);
		$existing->setItemGuid('item-guid');

		$this->expectOwnedCredential();
		$this->shareService->method('getACL')->with(null, 'item-guid')->willReturn($existing);
		$this->shareService->expects($this->never())->method('createACLEntry');
		$this->shareService->expects($this->once())
			->method('updateCredentialACL')
			->with($this->callback(static fn(SharingACL $acl): bool => $acl === $existing
				&& $acl->getPermissions() === PermissionEntity::READ
				&& $acl->getExpireViews() === 10))
			->willReturnArgument(0);
		$this->activityService->expects($this->never())->method('add');

		$this->controller->createPublicShare(5, 'item-guid', PermissionEntity::READ, 0, 10);
	}

	public function testApplyIntermediateShareRejectsUnreadableCredential(): void {
		$this->credentialService->expects($this->once())
			->method('getCredentialById')
			->with(5, self::USER)
			->willThrowException(new DoesNotExistException(''));
		$this->shareService->expects($this->never())->method('createBulkRequests');

		$response = $this->controller->applyIntermediateShare(5, 'item-guid', [['user_id' => 'bob']], PermissionEntity::READ);

		$this->assertInstanceOf(NotFoundJSONResponse::class, $response);
	}

	public function testApplyIntermediateShareRejectsMismatchingItemGuid(): void {
		$this->credentialService->method('getCredentialById')
			->with(5, self::USER)
			->willReturn($this->credential(5, 'own-guid'));
		$this->shareService->expects($this->never())->method('createBulkRequests');

		$response = $this->controller->applyIntermediateShare(5, 'item-guid', [['user_id' => 'bob']], PermissionEntity::READ);

		$this->assertInstanceOf(NotFoundJSONResponse::class, $response);
	}

	public function testUnshareCredentialRejectsForeignCredential(): void {
		$this->expectForeignCredential();
		$this->shareService->expects($this->never())->method('unshareCredential');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->unshareCredential('item-guid'));
	}

	public function testUnshareCredentialByOwner(): void {
		$this->expectOwnedCredential();
		$this->shareService->expects($this->once())->method('unshareCredential')->with('item-guid');

		$response = $this->controller->unshareCredential('item-guid');

		$this->assertSame(['result' => true], $response->getData());
	}

	public function testUnshareCredentialFromUserRejectsForeignCredential(): void {
		$this->expectForeignCredential();
		$this->shareService->expects($this->never())->method('getACL');
		$this->shareService->expects($this->never())->method('deleteShareACL');
		$this->shareService->expects($this->never())->method('cleanItemRequestsForUser');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->unshareCredentialFromUser('item-guid', 'bob'));
	}

	public function testUnshareCredentialFromUserByOwner(): void {
		$acl = new SharingACL();
		$acl->setUserId('bob');

		$this->expectOwnedCredential();
		$this->shareService->method('getACL')->with('bob', 'item-guid')->willReturn($acl);
		$this->shareService->method('getPendingShareRequestsForCredential')->willReturn([]);
		$this->shareService->expects($this->once())->method('deleteShareACL')->with($acl)->willReturnArgument(0);

		$response = $this->controller->unshareCredentialFromUser('item-guid', 'bob');

		$this->assertSame(['result' => true], $response->getData());
	}

	public function testSavePendingRequestRejectsRequestOfOtherUser(): void {
		$this->shareService->method('getRequestByGuid')
			->with('item-guid', 'vault-guid')
			->willReturn($this->shareRequest('bob'));
		$this->shareService->expects($this->never())->method('applyShare');
		$this->notificationService->expects($this->never())->method('credentialAcceptedSharedNotification');

		$response = $this->controller->savePendingRequest('item-guid', 'vault-guid', 'final-key');

		$this->assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testSavePendingRequestByTargetUser(): void {
		$this->shareService->method('getRequestByGuid')
			->with('item-guid', 'vault-guid')
			->willReturn($this->shareRequest(self::USER));
		$this->credentialService->method('getCredentialLabelById')->with(5)->willReturn($this->credential(5, 'item-guid', 'bob'));
		$this->notificationService->expects($this->once())->method('credentialAcceptedSharedNotification');
		$this->shareService->expects($this->once())->method('applyShare')->with('item-guid', 'vault-guid', 'final-key');

		$this->controller->savePendingRequest('item-guid', 'vault-guid', 'final-key');
	}

	public function testDeleteShareRequestRejectsRequestOfOtherUser(): void {
		$this->shareService->method('getShareRequestById')->with(9)->willReturn($this->shareRequest('bob'));
		$this->shareService->expects($this->never())->method('cleanItemRequestsForUser');
		$this->notificationService->expects($this->never())->method('credentialDeclinedSharedNotification');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->deleteShareRequest('9'));
	}

	public function testDeleteShareRequestByTargetUser(): void {
		$request = $this->shareRequest(self::USER);
		$this->shareService->method('getShareRequestById')->with(9)->willReturn($request);
		$this->credentialService->method('getCredentialLabelById')->with(5)->willReturn($this->credential(5, 'item-guid', 'bob'));
		$this->notificationService->expects($this->once())->method('credentialDeclinedSharedNotification');
		$this->shareService->expects($this->once())->method('cleanItemRequestsForUser')->with($request);

		$response = $this->controller->deleteShareRequest('9');

		$this->assertSame(['result' => true], $response->getData());
	}

	public function testGetPublicCredentialDataReturnsNotFoundForUnknownGuid(): void {
		$this->shareService->method('getACL')
			->with(null, 'item-guid')
			->willThrowException(new DoesNotExistException(''));
		$this->shareService->expects($this->never())->method('getSharedItem');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->getPublicCredentialData('item-guid'));
	}

	public function testGetPublicCredentialDataReturnsNotFoundForExpiredShare(): void {
		$acl = new SharingACL();
		$acl->setExpire(1);
		$acl->setExpireViews(5);
		$this->shareService->method('getACL')->willReturn($acl);
		$this->shareService->expects($this->never())->method('updateCredentialACL');
		$this->shareService->expects($this->never())->method('getSharedItem');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->getPublicCredentialData('item-guid'));
	}

	public function testGetPublicCredentialDataReturnsNotFoundWithoutRemainingViews(): void {
		$acl = new SharingACL();
		$acl->setExpire(0);
		$acl->setExpireViews(0);
		$this->shareService->method('getACL')->willReturn($acl);
		$this->shareService->expects($this->never())->method('getSharedItem');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->getPublicCredentialData('item-guid'));
	}

	public function testGetPublicCredentialDataDecrementsViews(): void {
		$acl = new SharingACL();
		$acl->setExpire(0);
		$acl->setExpireViews(5);
		$this->shareService->method('getACL')->with(null, 'item-guid')->willReturn($acl);
		$this->shareService->expects($this->once())
			->method('updateCredentialACL')
			->with($this->callback(static fn(SharingACL $entry): bool => $entry->getExpireViews() === 4))
			->willReturnArgument(0);
		$this->shareService->method('getSharedItem')->with(null, 'item-guid')->willReturn(['credential_data' => ['guid' => 'item-guid']]);

		$response = $this->controller->getPublicCredentialData('item-guid');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['credential_data' => ['guid' => 'item-guid']], $response->getData());
	}

	public function testGetRevisionsPassesUidToShareService(): void {
		$this->shareService->expects($this->once())
			->method('getItemHistory')
			->with(self::USER, 'item-guid')
			->willReturn([]);

		$response = $this->controller->getRevisions('item-guid');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([], $response->getData());
	}

	public function testUploadFileRejectsUserWithoutAcl(): void {
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->credential(5, 'item-guid', 'bob'));
		// also thrown for acl entries whose item_id does not match the credential
		$this->shareService->method('getCredentialACL')
			->with(self::USER, $this->isInstanceOf(Credential::class))
			->willThrowException(new DoesNotExistException(''));
		$this->fileService->expects($this->never())->method('createFile');

		$response = $this->controller->uploadFile('item-guid', 'data', 'file.txt', 'text/plain', 4);

		$this->assertInstanceOf(NotFoundJSONResponse::class, $response);
	}

	public function testUploadFileRejectsUserWithoutFilesPermission(): void {
		$acl = new SharingACL();
		$acl->setPermissions(PermissionEntity::READ);
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->credential(5, 'item-guid', 'bob'));
		$this->shareService->method('getCredentialACL')->with(self::USER, $this->isInstanceOf(Credential::class))->willReturn($acl);
		$this->fileService->expects($this->never())->method('createFile');

		$response = $this->controller->uploadFile('item-guid', 'data', 'file.txt', 'text/plain', 4);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
	}

	public function testGetFileRejectsMismatchingAcl(): void {
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->credential(5, 'item-guid', 'bob'));
		$this->shareService->method('getCredentialACL')
			->with(self::USER, $this->isInstanceOf(Credential::class))
			->willThrowException(new DoesNotExistException(''));
		$this->fileService->expects($this->never())->method('getFileByGuid');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->getFile('item-guid', 'file-guid'));
	}

	public function testGetFileReturnsFileOfCredentialOwner(): void {
		$acl = new SharingACL();
		$acl->setPermissions(PermissionEntity::READ | PermissionEntity::FILES);
		$acl->setExpire(0);
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->credential(5, 'item-guid', 'bob'));
		$this->shareService->method('getCredentialACL')->with(self::USER, $this->isInstanceOf(Credential::class))->willReturn($acl);
		$file = new File();
		$this->fileService->expects($this->once())->method('getFileByGuid')->with('file-guid', 'bob')->willReturn($file);

		$this->assertSame($file, $this->controller->getFile('item-guid', 'file-guid'));
	}
}
