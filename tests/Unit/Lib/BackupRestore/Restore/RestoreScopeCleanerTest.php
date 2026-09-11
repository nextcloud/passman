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
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreScopeCleaner;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\BackupRestore\ScopeReader;
use OCA\Passman\BackupRestore\ScopeSelection;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\DeleteVaultRequestMapper;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(RestoreScopeCleaner::class)]
class RestoreScopeCleanerTest extends TestCase {
	private ScopeReader&MockObject              $scopeReader;
	private VaultMapper&MockObject              $vaultMapper;
	private CredentialMapper&MockObject         $credentialMapper;
	private FileMapper&MockObject               $fileMapper;
	private CredentialRevisionMapper&MockObject $revisionMapper;
	private SharingACLMapper&MockObject         $sharingACLMapper;
	private ShareRequestMapper&MockObject       $shareRequestMapper;
	private DeleteVaultRequestMapper&MockObject $deleteVaultRequestMapper;
	private RestoreScopeCleaner                 $cleaner;

	protected function setUp(): void {
		parent::setUp();

		$this->scopeReader = $this->createMock(ScopeReader::class);
		$this->vaultMapper = $this->createMock(VaultMapper::class);
		$this->credentialMapper = $this->createMock(CredentialMapper::class);
		$this->fileMapper = $this->createMock(FileMapper::class);
		$this->revisionMapper = $this->createMock(CredentialRevisionMapper::class);
		$this->sharingACLMapper = $this->createMock(SharingACLMapper::class);
		$this->shareRequestMapper = $this->createMock(ShareRequestMapper::class);
		$this->deleteVaultRequestMapper = $this->createMock(DeleteVaultRequestMapper::class);

		$this->cleaner = new RestoreScopeCleaner(
			$this->scopeReader,
			$this->vaultMapper,
			$this->credentialMapper,
			$this->fileMapper,
			$this->revisionMapper,
			$this->sharingACLMapper,
			$this->shareRequestMapper,
			$this->deleteVaultRequestMapper,
		);
	}

	public function testReplaceDeletesEachScopedEntity(): void {
		$vault = Vault::fromRow(['id' => 1, 'guid' => 'vault-1']);
		$credential = Credential::fromRow(['id' => 2, 'guid' => 'cred-1']);
		$this->scopeReader->expects($this->once())
			->method('forManifest')
			->willReturn(new ScopeSelection(vaults: [$vault], credentials: [$credential]));

		$this->vaultMapper->expects($this->once())->method('delete')->with($vault);
		$this->credentialMapper->expects($this->once())->method('delete')->with($credential);
		$this->fileMapper->expects($this->never())->method('delete');
		$this->revisionMapper->expects($this->never())->method('delete');
		$this->sharingACLMapper->expects($this->never())->method('delete');
		$this->shareRequestMapper->expects($this->never())->method('delete');
		$this->deleteVaultRequestMapper->expects($this->never())->method('delete');

		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());
		$this->cleaner->clean(BackupArchiveFactory::manifest(), $context);

		$this->assertSame(1, $context->result->deleted[BackupArchive::SECTION_VAULTS]);
		$this->assertSame(1, $context->result->deleted[BackupArchive::SECTION_CREDENTIALS]);
	}

	public function testDryRunCountsDeletesWithoutCallingMapperDelete(): void {
		$vault = Vault::fromRow(['id' => 1, 'guid' => 'vault-1']);
		$credential = Credential::fromRow(['id' => 2, 'guid' => 'cred-1']);
		$this->scopeReader->expects($this->once())
			->method('forManifest')
			->willReturn(new ScopeSelection(vaults: [$vault], credentials: [$credential]));

		$this->vaultMapper->expects($this->never())->method('delete');
		$this->credentialMapper->expects($this->never())->method('delete');
		$this->fileMapper->expects($this->never())->method('delete');
		$this->revisionMapper->expects($this->never())->method('delete');
		$this->sharingACLMapper->expects($this->never())->method('delete');
		$this->shareRequestMapper->expects($this->never())->method('delete');
		$this->deleteVaultRequestMapper->expects($this->never())->method('delete');

		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult(), true);
		$this->cleaner->clean(BackupArchiveFactory::manifest(), $context);

		$this->assertSame(1, $context->result->deleted[BackupArchive::SECTION_VAULTS]);
		$this->assertSame(1, $context->result->deleted[BackupArchive::SECTION_CREDENTIALS]);
		$this->assertSame(0, $context->result->inserted[BackupArchive::SECTION_VAULTS]);
	}
}
