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

namespace OCA\Passman\Tests\Unit\Db;

use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Tests\Unit\Support\DbTestTrait;
use OCA\Passman\Utility\PermissionEntity;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[Group(name: 'DB')]
#[CoversClass(ShareRequestMapper::class)]
class ShareRequestMapperTest extends TestCase {
	use DbTestTrait;

	private const FROM_USER = 'passman_share_from_user';
	private const TARGET_USER = 'passman_share_target_user';

	private IDBConnection      $db;
	private ShareRequestMapper $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$this->mapper = new ShareRequestMapper($this->db);
		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deleteShareRequestsForUser($this->db, self::FROM_USER);
		$this->deleteShareRequestsForUser($this->db, self::TARGET_USER);
		$this->deleteShareRequestsForUser($this->db, self::TARGET_USER . '_2');
	}

	private function buildShareRequest(array $overrides = []): ShareRequest {
		$request = new ShareRequest();
		$request->setSharedKey('shared-key');
		$request->setCreated(1);
		$request->setItemGuid('item-guid-' . uniqid('', true));
		$request->setFromUserId(self::FROM_USER);
		$request->setPermissions(5);
		$request->setTargetVaultGuid('vault-guid-' . uniqid('', true));
		$request->setTargetUserId(self::TARGET_USER);
		$request->setTargetVaultId(5);
		$request->setItemId(50);

		foreach ($overrides as $method => $value) {
			$request->$method($value);
		}

		return $request;
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	/**
	 * @covers ::createRequest
	 * @covers ::getRequestByItemAndVaultGuid
	 */
	public function testCreateRequestAndGetByItemAndVaultGuid(): void {
		$request = $this->buildShareRequest();
		$inserted = $this->mapper->createRequest($request);

		$this->assertInstanceOf(ShareRequest::class, $inserted);
		$this->assertGreaterThan(0, $inserted->getId());

		$loaded = $this->mapper->getRequestByItemAndVaultGuid(
			$request->getItemGuid(),
			$request->getTargetVaultGuid(),
		);

		$this->assertInstanceOf(PermissionEntity::class, $loaded);
		$this->assertSame($inserted->jsonSerialize(), $loaded->jsonSerialize());
	}

	public function testGetRequestsByItemGuid(): void {
		$itemGuid = 'shared-item-' . uniqid('', true);
		$first = $this->mapper->createRequest($this->buildShareRequest([
			'setItemGuid'     => $itemGuid,
			'setTargetUserId' => self::TARGET_USER,
		]));
		$second = $this->mapper->createRequest($this->buildShareRequest([
			'setItemGuid'     => $itemGuid,
			'setTargetUserId' => self::TARGET_USER . '_2',
		]));

		$results = $this->mapper->getRequestsByItemGuid($itemGuid);

		$this->assertCount(2, $results);
		$ids = array_map(static fn(ShareRequest $r) => $r->getId(), $results);
		$this->assertContains($first->getId(), $ids);
		$this->assertContains($second->getId(), $ids);
	}

	public function testGetUserPendingRequests(): void {
		$request = $this->mapper->createRequest($this->buildShareRequest());

		$results = $this->mapper->getUserPendingRequests(self::TARGET_USER);

		$this->assertCount(1, $results);
		$this->assertSame($request->getId(), $results[0]->getId());
	}

	public function testCleanItemRequestsForUser(): void {
		$itemId = 12345;
		$request = $this->mapper->createRequest($this->buildShareRequest([
			'setItemId' => $itemId,
		]));

		$this->mapper->cleanItemRequestsForUser($itemId, self::TARGET_USER);

		$remaining = $this->mapper->getUserPendingRequests(self::TARGET_USER);
		foreach ($remaining as $row) {
			if ($row->getItemId() === $itemId) {
				$this->fail('Pending request for cleaned item still exists');
			}
		}

		$this->assertSame($request->getItemId(), $itemId);
	}

	public function testGetShareRequestById(): void {
		$request = $this->mapper->createRequest($this->buildShareRequest());

		$loaded = $this->mapper->getShareRequestById($request->getId());
		$this->assertEquals($request->jsonSerialize(), $loaded->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getShareRequestById(PHP_INT_MAX);
	}

	public function testDeleteShareRequest(): void {
		$request = $this->mapper->createRequest($this->buildShareRequest());

		$deleted = $this->mapper->deleteShareRequest($request);
		$this->assertInstanceOf(ShareRequest::class, $deleted);

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getShareRequestById($request->getId());
	}

	public function testUpdateShareRequest(): void {
		$request = $this->mapper->createRequest($this->buildShareRequest());
		$request->setTargetVaultId($request->getTargetVaultId() + 50);

		$updated = $this->mapper->updateShareRequest($request);
		$this->assertSame($request->getTargetVaultId(), $updated->getTargetVaultId());

		$fromDb = $this->mapper->getShareRequestById($request->getId());
		$this->assertSame($request->getTargetVaultId(), $fromDb->getTargetVaultId());
	}

	/**
	 * @covers ::getPendingShareRequests
	 * @covers ::updatePendingRequestPermissions
	 */
	public function testGetPendingShareRequestsAndUpdatePermissions(): void {
		$itemGuid = 'pending-item-' . uniqid('', true);
		$request = $this->mapper->createRequest($this->buildShareRequest([
			'setItemGuid'    => $itemGuid,
			'setPermissions' => 1,
		]));

		$pending = $this->mapper->getPendingShareRequests($itemGuid, self::TARGET_USER);
		$this->assertCount(1, $pending);
		$this->assertSame($request->getId(), $pending[0]->getId());

		$newPermissions = $request->getPermissions() + 4;
		$this->mapper->updatePendingRequestPermissions($itemGuid, self::TARGET_USER, $newPermissions);

		$afterUpdate = $this->mapper->getPendingShareRequests($itemGuid, self::TARGET_USER);
		$this->assertSame($newPermissions, $afterUpdate[0]->getPermissions());
	}
}
