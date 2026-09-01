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
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\Restore\CredentialRestorer;
use OCA\Passman\BackupRestore\Restore\EntityWriter;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\Restore\RestoreWarnings;
use OCA\Passman\BackupRestore\Restore\ServerEncryptionApplier;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(CredentialRestorer::class)]
class CredentialRestorerTest extends TestCase {
	private CredentialMapper&MockObject        $credentialMapper;
	private RestoreLookup&MockObject           $lookup;
	private ServerEncryptionApplier&MockObject $encryptionApplier;
	private CredentialRestorer                 $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->credentialMapper = $this->createMock(CredentialMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->encryptionApplier = $this->createMock(ServerEncryptionApplier::class);
		$this->restorer = new CredentialRestorer(
			$this->credentialMapper,
			new EntityWriter(),
			$this->lookup,
			$this->encryptionApplier,
		);
	}

	public function testSkipsWhenTheParentVaultIsUnmapped(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_CREDENTIALS => [[
				'id' => 2,
				'guid' => 'cred-1',
				'vault_id' => 10,
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->credentialMapper->expects($this->never())->method('insert');
		$this->credentialMapper->expects($this->never())->method('update');
		$this->lookup->expects($this->never())->method('findCredential');
		$this->encryptionApplier->expects($this->never())->method('credentialRow');

		$this->restorer->restore($archive, $context);

		$this->assertSame(1, $context->result->skipped[BackupArchive::SECTION_CREDENTIALS]);
		$this->assertSame(
			[RestoreWarnings::vaultOfCredentialMissing('cred-1', 10)],
			$context->result->warnings
		);
	}

	public function testPortablePathReencryptsThenRemembersMappedIds(): void {
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_PORTABLE),
			[
				BackupArchive::SECTION_CREDENTIALS => [[
					'id' => 2,
					'guid' => 'cred-1',
					'vault_id' => 10,
					'username' => 'alice',
				]],
			],
		);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());
		$context->rememberVault(10, 100);

		$this->lookup->expects($this->never())->method('findCredential');
		$this->encryptionApplier->expects($this->once())
			->method('credentialRow')
			->with($this->callback(static fn(array $row): bool => $row['vault_id'] === 100))
			->willReturnCallback(static fn(array $row): array => $row);
		$this->credentialMapper->expects($this->once())
			->method('insert')
			->with($this->callback(static fn(Credential $entity): bool => $entity->getVaultId() === 100))
			->willReturnCallback(static function (Credential $entity): Credential {
				$entity->setId(200);
				return $entity;
			});

		$this->restorer->restore($archive, $context);

		$this->assertSame(200, $context->credentialId(2));
	}

	public function testRawPathDoesNotEncryptAndMergeLooksUpGuid(): void {
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_RAW),
			[
				BackupArchive::SECTION_CREDENTIALS => [[
					'id' => 2,
					'guid' => 'cred-1',
					'vault_id' => 10,
				]],
			],
		);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$context->rememberVault(10, 100);
		$existing = Credential::fromRow(['id' => 55, 'guid' => 'cred-1']);

		$this->encryptionApplier->expects($this->never())->method('credentialRow');
		$this->lookup->expects($this->once())->method('findCredential')->with('cred-1')->willReturn($existing);
		$this->credentialMapper->expects($this->never())->method('insert');
		$this->credentialMapper->expects($this->once())
			->method('update')
			->with($this->callback(static function (Credential $entity): bool {
				return $entity->getId() === 55 && $entity->getVaultId() === 100;
			}))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);

		$this->assertSame(55, $context->credentialId(2));
	}
}
