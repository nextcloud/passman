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

use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Tests\Unit\Support\DbTestTrait;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[Group(name: 'DB')]
#[CoversClass(SharingACLMapper::class)]
class SharingACLMapperTest extends TestCase {
	use DbTestTrait;

	private const TEST_USER = 'passman_acl_mapper_test';

	private IDBConnection    $db;
	private SharingACLMapper $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$this->mapper = new SharingACLMapper($this->db);
		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deletePassmanRows($this->db, 'passman_sharing_acl', 'user_id', self::TEST_USER);
		$this->deletePassmanRows($this->db, 'passman_sharing_acl', 'user_id', self::TEST_USER . '_2');
		foreach ([self::TEST_USER, self::TEST_USER . '_2'] as $userId) {
			$this->deletePassmanRows($this->db, 'passman_credentials', 'user_id', $userId);
			$this->deletePassmanRows($this->db, 'passman_vaults', 'user_id', $userId);
		}
	}

	private function createCredential(string $userId): Credential {
		$vault = (new VaultMapper($this->db, new Utils()))->create('Test vault', $userId);
		return (new CredentialMapper($this->db, new Utils()))->create($this->sampleCredentialData($vault->getId(), $userId));
	}

	private function buildAcl(array $overrides = []): SharingACL {
		$acl = new SharingACL();
		$acl->setItemId(10);
		$acl->setItemGuid('item-guid-' . uniqid('', true));
		$acl->setUserId(self::TEST_USER);
		$acl->setCreated(Utils::getTime());
		$acl->setExpire(0);
		$acl->setExpireViews(0);
		$acl->setPermissions(7);
		$acl->setVaultId(3);
		$acl->setVaultGuid('vault-guid-' . uniqid('', true));
		$acl->setSharedKey('shared-key');

		foreach ($overrides as $method => $value) {
			$acl->$method($value);
		}

		return $acl;
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	public function testCreateAndGetItemACL(): void {
		$acl = $this->mapper->createACLEntry($this->buildAcl());

		$loaded = $this->mapper->getItemACL(self::TEST_USER, $acl->getItemGuid());

		$this->assertInstanceOf(SharingACL::class, $loaded);
		$this->assertEquals($acl->jsonSerialize(), $loaded->jsonSerialize());
	}

	public function testGetVaultEntries(): void {
		$vaultGuid = 'vault-guid-' . uniqid('', true);
		$first = $this->mapper->createACLEntry($this->buildAcl([
			'setVaultGuid' => $vaultGuid,
			'setItemGuid'  => 'item-a-' . uniqid('', true),
		]));
		$second = $this->mapper->createACLEntry($this->buildAcl([
			'setVaultGuid' => $vaultGuid,
			'setItemGuid'  => 'item-b-' . uniqid('', true),
		]));

		$entries = $this->mapper->getVaultEntries(self::TEST_USER, $vaultGuid);

		$this->assertCount(2, $entries);
		$ids = array_map(static fn(SharingACL $entry) => $entry->getId(), $entries);
		$this->assertContains($first->getId(), $ids);
		$this->assertContains($second->getId(), $ids);
	}

	public function testUpdateCredentialACL(): void {
		$acl = $this->mapper->createACLEntry($this->buildAcl());
		$originalExpire = $acl->getExpire();

		$acl->setExpire(Utils::getTime() + 500);
		$this->mapper->updateCredentialACL($acl);

		$updated = $this->mapper->getItemACL(self::TEST_USER, $acl->getItemGuid());
		$this->assertSame($acl->getId(), $updated->getId());
		$this->assertNotSame($originalExpire, $updated->getExpire());
		$this->assertSame($acl->getExpire(), $updated->getExpire());
	}

	public function testGetCredentialAclList(): void {
		$itemGuid = 'shared-item-' . uniqid('', true);
		$first = $this->mapper->createACLEntry($this->buildAcl([
			'setItemGuid' => $itemGuid,
			'setUserId'   => self::TEST_USER,
		]));
		$second = $this->mapper->createACLEntry($this->buildAcl([
			'setItemGuid' => $itemGuid,
			'setUserId'   => self::TEST_USER . '_2',
		]));

		$list = $this->mapper->getCredentialAclList($itemGuid);

		$this->assertCount(2, $list);
		$ids = array_map(static fn(SharingACL $entry) => $entry->getId(), $list);
		$this->assertContains($first->getId(), $ids);
		$this->assertContains($second->getId(), $ids);
	}

	public function testDeleteShareACL(): void {
		$acl = $this->mapper->createACLEntry($this->buildAcl());

		$this->mapper->deleteShareACL($acl);

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getItemACL(self::TEST_USER, $acl->getItemGuid());
	}

	public function testFindEntriesWithMismatchingItemId(): void {
		$ownCredential = $this->createCredential(self::TEST_USER);
		$foreignCredential = $this->createCredential(self::TEST_USER . '_2');

		$valid = $this->mapper->createACLEntry($this->buildAcl([
			'setItemId'   => $ownCredential->getId(),
			'setItemGuid' => $ownCredential->getGuid(),
		]));
		$foreign = $this->mapper->createACLEntry($this->buildAcl([
			'setItemId'   => $foreignCredential->getId(),
			'setItemGuid' => $ownCredential->getGuid(),
		]));
		$missing = $this->mapper->createACLEntry($this->buildAcl([
			'setItemId'   => PHP_INT_MAX,
			'setItemGuid' => $ownCredential->getGuid(),
		]));

		$rows = [];
		foreach ($this->mapper->findEntriesWithMismatchingItemId() as $row) {
			$rows[(int)$row['id']] = $row;
		}

		$this->assertArrayNotHasKey($valid->getId(), $rows);

		$this->assertArrayHasKey($foreign->getId(), $rows);
		$this->assertSame($foreignCredential->getGuid(), $rows[$foreign->getId()]['item_id_credential_guid']);
		$this->assertSame(self::TEST_USER . '_2', $rows[$foreign->getId()]['item_id_credential_owner']);
		$this->assertSame(self::TEST_USER, $rows[$foreign->getId()]['item_guid_credential_owner']);
		$this->assertEquals($ownCredential->getId(), $rows[$foreign->getId()]['item_guid_credential_id']);

		$this->assertArrayHasKey($missing->getId(), $rows);
		$this->assertNull($rows[$missing->getId()]['item_id_credential_guid']);
		$this->assertNull($rows[$missing->getId()]['item_id_credential_owner']);
		$this->assertSame(self::TEST_USER, $rows[$missing->getId()]['item_guid_credential_owner']);
	}
}
