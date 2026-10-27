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
use OCA\Passman\Controller\CredentialController;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(CredentialController::class)]
class CredentialControllerTest extends TestCase {
	private const USER = 'alice';

	private CredentialService&MockObject         $credentialService;
	private CredentialRevisionService&MockObject $revisionService;
	private ShareService&MockObject              $shareService;
	private CredentialController                 $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialService = $this->createMock(CredentialService::class);
		$this->revisionService = $this->createMock(CredentialRevisionService::class);
		$this->shareService = $this->createMock(ShareService::class);

		$this->controller = new CredentialController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			self::USER,
			$this->credentialService,
			$this->createMock(ActivityService::class),
			$this->revisionService,
			$this->shareService,
			$this->createMock(SettingsService::class),
		);
	}

	private function foreignCredential(): Credential {
		$credential = new Credential();
		$credential->setId(5);
		$credential->setGuid('item-guid');
		$credential->setUserId('bob');
		$credential->setLabel('label');
		return $credential;
	}

	public function testUpdateCredentialRejectsUserWithoutMatchingAcl(): void {
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->foreignCredential());
		// also thrown for acl entries whose item_id does not match the credential
		$this->shareService->expects($this->once())
			->method('getCredentialACL')
			->with(self::USER, $this->isInstanceOf(Credential::class))
			->willThrowException(new DoesNotExistException(''));
		$this->credentialService->expects($this->never())->method('updateCredential');

		$parameters = array_fill_keys(array_map(
			static fn(\ReflectionParameter $parameter): string => $parameter->getName(),
			(new \ReflectionMethod(CredentialController::class, 'updateCredential'))->getParameters(),
		), null);
		$parameters['credential_guid'] = 'item-guid';

		$response = $this->controller->updateCredential(...$parameters);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
	}

	public function testGetRevisionRejectsUserWithoutMatchingAcl(): void {
		$this->credentialService->method('getCredentialByGUID')->with('item-guid')->willReturn($this->foreignCredential());
		$this->shareService->method('getCredentialACL')
			->with(self::USER, $this->isInstanceOf(Credential::class))
			->willThrowException(new DoesNotExistException(''));
		$this->revisionService->expects($this->never())->method('getRevisions');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $this->controller->getRevision('item-guid'));
	}

	public function testUpdateRevisionRejectsRevisionOfOtherUser(): void {
		// revisions of other users are not found, since the lookup is restricted to the current user
		$this->revisionService->expects($this->once())
			->method('getRevision')
			->with(7, self::USER)
			->willThrowException(new DoesNotExistException(''));
		$this->revisionService->expects($this->never())->method('updateRevision');

		$response = $this->controller->updateRevision('7', 'data');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame([], $response->getData());
	}

	public function testUpdateRevisionOfOwnRevision(): void {
		$revision = new CredentialRevision();
		$this->revisionService->method('getRevision')->with(7, self::USER)->willReturn($revision);
		$this->revisionService->expects($this->once())
			->method('updateRevision')
			->with($this->callback(static fn(CredentialRevision $updated): bool => $updated === $revision && $updated->getCredentialData() === 'data'))
			->willReturnArgument(0);

		$this->controller->updateRevision('7', 'data');
	}

	public function testDeleteRevisionReturnsNotFoundForForeignOrMissingRevision(): void {
		// The credential is owned by the caller, but the revision lookup throws because the
		// revision does not exist, is not owned by the caller, or belongs to another credential.
		// The controller must translate that into a 404, not delete and not 500.
		$this->credentialService->method('getCredentialByGUID')
			->with('item-guid', self::USER)
			->willReturn($this->foreignCredential());
		$this->revisionService->expects($this->once())
			->method('deleteRevision')
			->with(7, self::USER, 5)
			->willThrowException(new DoesNotExistException(''));

		$response = $this->controller->deleteRevision('item-guid', '7');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $response);
		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function testDeleteRevisionReturnsNotFoundForForeignCredential(): void {
		// The credential guid does not resolve for this user: nothing must be deleted.
		$this->credentialService->method('getCredentialByGUID')
			->with('item-guid', self::USER)
			->willThrowException(new DoesNotExistException(''));
		$this->revisionService->expects($this->never())->method('deleteRevision');

		$response = $this->controller->deleteRevision('item-guid', '7');

		$this->assertInstanceOf(NotFoundJSONResponse::class, $response);
		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function testDeleteRevisionDeletesOwnRevision(): void {
		// foreignCredential()->getId() is 5; the revision must be cross-validated against it.
		$revision = new CredentialRevision();
		$this->credentialService->method('getCredentialByGUID')
			->with('item-guid', self::USER)
			->willReturn($this->foreignCredential());
		$this->revisionService->expects($this->once())
			->method('deleteRevision')
			->with(7, self::USER, 5)
			->willReturn($revision);

		$response = $this->controller->deleteRevision('item-guid', '7');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}
}
