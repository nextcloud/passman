<?php
/**
 * Nextcloud - Passman
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

namespace OCA\Passman\Tests\Unit\Lib\BackupRestore\Restore;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\Restore\EntityWriter;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\Restore\VaultRestorer;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(VaultRestorer::class)]
class VaultRestorerTest extends TestCase {
	private VaultMapper&MockObject   $vaultMapper;
	private RestoreLookup&MockObject $lookup;
	private VaultRestorer            $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->vaultMapper = $this->createMock(VaultMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->restorer = new VaultRestorer($this->vaultMapper, new EntityWriter(), $this->lookup);
	}

	public function testReplaceDoesNotLookUpExistingGuidAndRemembersMappedIds(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_VAULTS => [['id' => 10, 'guid' => 'vault-1', 'name' => 'Personal']],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->never())->method('findVault');
		$this->vaultMapper->expects($this->never())->method('update');
		$this->vaultMapper->expects($this->once())
			->method('insert')
			->willReturnCallback(static function (Vault $entity): Vault {
				$entity->setId(100);
				return $entity;
			});

		$this->restorer->restore($archive, $context);

		$this->assertSame(100, $context->vaultId(10));
		$this->assertSame(1, $context->result->inserted[BackupArchive::SECTION_VAULTS]);
	}

	public function testMergeLooksUpGuidAndUpdatesTheExistingRow(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_VAULTS => [['id' => 10, 'guid' => 'vault-1']],
		]);
		$existing = Vault::fromRow(['id' => 42, 'guid' => 'vault-1']);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());

		$this->lookup->expects($this->once())->method('findVault')->with('vault-1')->willReturn($existing);
		$this->vaultMapper->expects($this->never())->method('insert');
		$this->vaultMapper->expects($this->once())
			->method('update')
			->with($this->callback(static fn(Vault $entity): bool => $entity->getId() === 42))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);

		$this->assertSame(42, $context->vaultId(10));
		$this->assertSame(1, $context->result->updated[BackupArchive::SECTION_VAULTS]);
	}
}
