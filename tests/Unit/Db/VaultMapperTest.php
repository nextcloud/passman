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

use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[Group(name: 'DB')]
#[CoversClass(VaultMapper::class)]
class VaultMapperTest extends TestCase {
	private const TEST_USER = 'passman_mapper_test_user';

	private IDBConnection $db;
	private VaultMapper   $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$this->mapper = new VaultMapper($this->db, new Utils());
		$this->resetVaults();
	}

	protected function tearDown(): void {
		$this->resetVaults();
		parent::tearDown();
	}

	private function resetVaults(): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete('passman_vaults')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter(self::TEST_USER)));
		$qb->executeStatement();
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	public function testFindByGuid(): void {
		$vault = $this->mapper->create('Example vault', self::TEST_USER);

		$found = $this->mapper->findByGuid($vault->getGuid(), self::TEST_USER);
		$this->assertInstanceOf(Vault::class, $found);
		$this->assertSame($vault->getGuid(), $found->getGuid());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->findByGuid('asdf', 'fdsa');
	}

	public function testFindVaultsFromUser(): void {
		$this->mapper->create('Vault A', self::TEST_USER);
		$this->mapper->create('Vault B', self::TEST_USER);

		$vaults = $this->mapper->findVaultsFromUser(self::TEST_USER);

		$this->assertCount(2, $vaults);
		$this->assertInstanceOf(Vault::class, $vaults[0]);
		$this->assertSame(self::TEST_USER, $vaults[0]->getUserId());
	}

	public function testUpdateVault(): void {
		$vault = $this->mapper->create('Original name', self::TEST_USER);
		$vault->setName('ASDF');

		$this->mapper->updateVault($vault);
		$updated = $this->mapper->findByGuid($vault->getGuid(), self::TEST_USER);

		$this->assertSame('ASDF', $updated->getName());
		$this->assertNotSame('Original name', $updated->getName());
	}

	public function testSetLastAccess(): void {
		$vault = $this->mapper->create('Access test', self::TEST_USER);
		$before = $vault->getLastAccess();
		$expectedTime = Utils::getTime();

		$this->mapper->setLastAccess($vault->getId(), self::TEST_USER);
		$updated = $this->mapper->findByGuid($vault->getGuid(), self::TEST_USER);

		$this->assertNotSame($before, $updated->getLastAccess());
		$this->assertSame($expectedTime, $updated->getLastAccess());
	}

	public function testCreate(): void {
		$vault = $this->mapper->create('test vault name', self::TEST_USER);
		$vaultsInDb = $this->mapper->findVaultsFromUser(self::TEST_USER);

		$this->assertInstanceOf(Vault::class, $vault);
		$this->assertSame('test vault name', $vault->getName());
		$this->assertSame(self::TEST_USER, $vault->getUserId());
		$this->assertCount(1, $vaultsInDb);
		$this->assertEquals($vault->jsonSerialize(), $vaultsInDb[0]->jsonSerialize());
	}

	public function testUpdateSharingKeys(): void {
		$vault = $this->mapper->create('Sharing keys', self::TEST_USER);
		$privateKey = 'a private key';
		$publicKey = 'a public key';

		$this->mapper->updateSharingKeys($vault->getId(), $privateKey, $publicKey);
		$updated = $this->mapper->findByGuid($vault->getGuid(), self::TEST_USER);

		$this->assertSame($privateKey, $updated->getPrivateSharingKey());
		$this->assertSame($publicKey, $updated->getPublicSharingKey());
	}
}
