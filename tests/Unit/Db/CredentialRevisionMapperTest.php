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

use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Tests\Unit\Support\DbTestTrait;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[Group(name: 'DB')]
#[CoversClass(\OCA\Passman\Db\CredentialRevisionMapper::class)]
class CredentialRevisionMapperTest extends TestCase {
	use DbTestTrait;

	private const TEST_USER = 'passman_revision_mapper_test';
	private const EDITED_BY = 'passman_revision_editor';

	private IDBConnection $db;
	private VaultMapper $vaultMapper;
	private CredentialMapper $credentialMapper;
	private CredentialRevisionMapper $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$utils = new Utils();
		$this->vaultMapper = new VaultMapper($this->db, $utils);
		$this->credentialMapper = new CredentialMapper($this->db, $utils);
		$this->mapper = new CredentialRevisionMapper($this->db, $utils);
		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deletePassmanRows($this->db, 'passman_revisions', 'user_id', self::TEST_USER);
		$this->deletePassmanRows($this->db, 'passman_credentials', 'user_id', self::TEST_USER);
		$this->deletePassmanRows($this->db, 'passman_vaults', 'user_id', self::TEST_USER);
	}

	private function createCredentialId(): int {
		$vault = $this->vaultMapper->create('Revision vault', self::TEST_USER);
		$credential = $this->credentialMapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER));
		return $credential->getId();
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	/**
	 * @covers ::create
	 * @covers ::getRevision
	 */
	public function testCreateAndGetRevision(): void {
		$credentialId = $this->createCredentialId();
		$payload = 'credential data stuff';

		$revision = $this->mapper->create($payload, self::TEST_USER, $credentialId, self::EDITED_BY);

		$this->assertSame($payload, json_decode(base64_decode($revision->getCredentialData())));
		$this->assertSame(self::EDITED_BY, $revision->getEditedBy());
		$this->assertSame(self::TEST_USER, $revision->getUserId());
		$this->assertSame($credentialId, $revision->getCredentialId());
		$this->assertGreaterThan(0, $revision->getId());

		$loaded = $this->mapper->getRevision($revision->getId());
		$this->assertEquals($revision->jsonSerialize(), $loaded->jsonSerialize());

		$loadedForUser = $this->mapper->getRevision($revision->getId(), self::TEST_USER);
		$this->assertEquals($revision->jsonSerialize(), $loadedForUser->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getRevision(PHP_INT_MAX);
	}

	public function testGetRevisions(): void {
		$credentialId = $this->createCredentialId();
		$first = $this->mapper->create('first', self::TEST_USER, $credentialId, self::EDITED_BY);
		$second = $this->mapper->create('second', self::TEST_USER, $credentialId, self::EDITED_BY);

		$all = $this->mapper->getRevisions($credentialId);
		$this->assertCount(2, $all);

		$forUser = $this->mapper->getRevisions($credentialId, self::TEST_USER);
		$this->assertCount(2, $forUser);
		$this->assertContainsOnlyInstancesOf(CredentialRevision::class, $forUser);

		$ids = array_map(static fn (CredentialRevision $r) => $r->getId(), $forUser);
		$this->assertContains($first->getId(), $ids);
		$this->assertContains($second->getId(), $ids);

		$this->assertCount(0, $this->mapper->getRevisions(PHP_INT_MAX));
	}

	public function testDeleteRevision(): void {
		$credentialId = $this->createCredentialId();
		$revision = $this->mapper->create('to delete', self::TEST_USER, $credentialId, self::EDITED_BY);

		$this->mapper->deleteRevision($revision->getId(), self::TEST_USER);

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getRevision($revision->getId(), self::TEST_USER);
	}
}
