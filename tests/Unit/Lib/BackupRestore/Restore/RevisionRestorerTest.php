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
use OCA\Passman\BackupRestore\Restore\EntityWriter;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\Restore\RestoreWarnings;
use OCA\Passman\BackupRestore\Restore\RevisionRestorer;
use OCA\Passman\BackupRestore\Restore\ServerEncryptionApplier;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(RevisionRestorer::class)]
class RevisionRestorerTest extends TestCase {
	private CredentialRevisionMapper&MockObject $revisionMapper;
	private RestoreLookup&MockObject            $lookup;
	private ServerEncryptionApplier&MockObject  $encryptionApplier;
	private RevisionRestorer                    $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->revisionMapper = $this->createMock(CredentialRevisionMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->encryptionApplier = $this->createMock(ServerEncryptionApplier::class);
		$this->restorer = new RevisionRestorer(
			$this->revisionMapper,
			new EntityWriter(),
			$this->lookup,
			$this->encryptionApplier,
		);
	}

	public function testSkipsWhenTheParentCredentialIsUnmapped(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_REVISIONS => [[
				'id'            => 8,
				'guid'          => 'rev-1',
				'credential_id' => 2,
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->revisionMapper->expects($this->never())->method('insert');
		$this->revisionMapper->expects($this->never())->method('update');
		$this->lookup->expects($this->never())->method('revisionIdsByGuid');
		$this->encryptionApplier->expects($this->never())->method('revisionData');

		$this->restorer->restore($archive, $context);

		$this->assertSame(
			[RestoreWarnings::credentialOfRevisionMissing('rev-1', 2)],
			$context->result->warnings
		);
	}

	public function testPortablePathReencodesCredentialData(): void {
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_PORTABLE),
			[
				BackupArchive::SECTION_REVISIONS => [[
					'id'              => 8,
					'guid'            => 'rev-1',
					'credential_id'   => 2,
					'credential_data' => ['label' => 'Note'],
				]],
			],
		);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());
		$context->rememberCredential(2, 20);

		$this->lookup->expects($this->never())->method('revisionIdsByGuid');
		$this->encryptionApplier->expects($this->once())
			->method('revisionData')
			->with(['label' => 'Note'], 'rev-1')
			->willReturn('encoded-blob');
		$this->revisionMapper->expects($this->once())
			->method('insert')
			->with($this->callback(static function (CredentialRevision $entity): bool {
				return $entity->getCredentialId() === 20 && $entity->getCredentialData() === 'encoded-blob';
			}))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}

	public function testMergeLooksUpExistingRevisionByGuid(): void {
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_RAW),
			[
				BackupArchive::SECTION_REVISIONS => [[
					'id'            => 8,
					'guid'          => 'rev-1',
					'credential_id' => 2,
				]],
			],
		);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$context->rememberCredential(2, 20);

		$this->encryptionApplier->expects($this->never())->method('revisionData');
		$this->lookup->expects($this->once())->method('revisionIdsByGuid')->with(20)->willReturn(['rev-1' => 11]);
		$this->revisionMapper->expects($this->never())->method('insert');
		$this->revisionMapper->expects($this->once())
			->method('update')
			->with($this->callback(static function (CredentialRevision $entity): bool {
				return $entity->getId() === 11 && $entity->getCredentialId() === 20;
			}))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}
}
