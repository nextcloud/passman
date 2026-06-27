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
#[CoversClass(\OCA\Passman\Db\CredentialMapper::class)]
class CredentialMapperTest extends TestCase {
	use DbTestTrait;

	private const TEST_USER = 'passman_credential_mapper_test';

	private IDBConnection $db;
	private VaultMapper $vaultMapper;
	private CredentialMapper $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$this->vaultMapper = new VaultMapper($this->db, new Utils());
		$this->mapper = new CredentialMapper($this->db, new Utils());
		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deletePassmanRows($this->db, 'passman_credentials', 'user_id', self::TEST_USER);
		$this->deletePassmanRows($this->db, 'passman_vaults', 'user_id', self::TEST_USER);
	}

	private function createVaultAndCredential(array $overrides = []): Credential {
		$vault = $this->vaultMapper->create('Test vault', self::TEST_USER);
		return $this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, $overrides));
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	/**
	 * @covers ::create
	 * @covers ::getCredentialById
	 */
	public function testCreateAndGetCredentialById(): void {
		$created = $this->createVaultAndCredential(['label' => 'created credential']);

		$byId = $this->mapper->getCredentialById($created->getId());
		$this->assertEquals($created->jsonSerialize(), $byId->jsonSerialize());

		$byIdAndUser = $this->mapper->getCredentialById($created->getId(), self::TEST_USER);
		$this->assertEquals($created->jsonSerialize(), $byIdAndUser->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getCredentialById(PHP_INT_MAX);
	}

	public function testGetCredentialsByVaultId(): void {
		$vault = $this->vaultMapper->create('Vault', self::TEST_USER);
		$first = $this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, ['label' => 'A']));
		$second = $this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, ['label' => 'B']));

		$credentials = $this->mapper->getCredentialsByVaultId((string)$vault->getId(), self::TEST_USER);

		$this->assertCount(2, $credentials);
		$labels = array_map(static fn (Credential $c) => $c->getLabel(), $credentials);
		$this->assertContains($first->getLabel(), $labels);
		$this->assertContains($second->getLabel(), $labels);
	}

	public function testGetRandomCredentialByVaultId(): void {
		$vault = $this->vaultMapper->create('Vault', self::TEST_USER);
		$this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, ['label' => 'random pick']));

		$credential = $this->mapper->getRandomCredentialByVaultId((string)$vault->getId(), self::TEST_USER);

		$this->assertInstanceOf(Credential::class, $credential);
		$this->assertSame(self::TEST_USER, $credential->getUserId());
		$this->assertSame($vault->getId(), $credential->getVaultId());
	}

	public function testGetExpiredCredentials(): void {
		$vault = $this->vaultMapper->create('Vault', self::TEST_USER);
		$expired = $this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, [
			'label' => 'expired',
			'expire_time' => Utils::getTime() - 100,
		]));
		$this->mapper->create($this->sampleCredentialData($vault->getId(), self::TEST_USER, [
			'label' => 'valid',
			'expire_time' => Utils::getTime() + 3600,
		]));

		$results = $this->mapper->getExpiredCredentials(Utils::getTime());

		$ids = array_map(static fn (Credential $c) => $c->getId(), $results);
		$this->assertContains($expired->getId(), $ids);
	}

	public function testGetCredentialLabelById(): void {
		$created = $this->createVaultAndCredential(['label' => 'label lookup']);

		$partial = $this->mapper->getCredentialLabelById($created->getId());

		$this->assertSame($created->getId(), $partial->getId());
		$this->assertSame('label lookup', $partial->getLabel());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getCredentialLabelById(PHP_INT_MAX);
	}

	public function testGetCredentialByGUID(): void {
		$created = $this->createVaultAndCredential();

		$byGuid = $this->mapper->getCredentialByGUID($created->getGuid());
		$this->assertEquals($created->jsonSerialize(), $byGuid->jsonSerialize());

		$byGuidAndUser = $this->mapper->getCredentialByGUID($created->getGuid(), self::TEST_USER);
		$this->assertEquals($created->jsonSerialize(), $byGuidAndUser->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getCredentialByGUID('missing-guid');
	}

	public function testUpdateCredential(): void {
		$created = $this->createVaultAndCredential(['label' => 'before']);

		$updated = $this->mapper->updateCredential([
			'guid' => $created->getGuid(),
			'label' => 'after',
			'description' => 'updated description',
			'tags' => 'new-tag',
			'email' => 'new@example.com',
			'username' => 'new-user',
			'password' => 'new-pass',
			'url' => 'https://new.example.com',
			'icon' => 'icon-data',
			'renew_interval' => 10,
			'expire_time' => Utils::getTime() + 7200,
			'files' => '{files}',
			'custom_fields' => '{fields}',
			'otp' => 'otp',
			'hidden' => true,
			'delete_time' => Utils::getTime() + 1000,
			'compromised' => null,
			'shared_key' => 'shared',
		], false);

		$this->assertSame('after', $updated->getLabel());
		$this->assertSame('updated description', $updated->getDescription());

		$fromDb = $this->mapper->getCredentialByGUID($created->getGuid());
		$this->assertSame('after', $fromDb->getLabel());
	}

	public function testDeleteCredential(): void {
		$created = $this->createVaultAndCredential();

		$this->assertEquals($created, $this->mapper->deleteCredential($created));

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getCredentialByGUID($created->getGuid());
	}

	public function testUpd(): void {
		$created = $this->createVaultAndCredential(['url' => 'https://before.example.com']);
		$created->setUrl('https://after.example.com');

		$this->mapper->upd($created);

		$fromDb = $this->mapper->getCredentialById($created->getId());
		$this->assertSame('https://after.example.com', $fromDb->getUrl());
	}
}
