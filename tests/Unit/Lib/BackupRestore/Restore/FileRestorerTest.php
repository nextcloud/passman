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
use OCA\Passman\BackupRestore\Restore\FileRestorer;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\Restore\ServerEncryptionApplier;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(FileRestorer::class)]
class FileRestorerTest extends TestCase {
	private FileMapper&MockObject              $fileMapper;
	private RestoreLookup&MockObject           $lookup;
	private ServerEncryptionApplier&MockObject $encryptionApplier;
	private FileRestorer                       $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->fileMapper = $this->createMock(FileMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->encryptionApplier = $this->createMock(ServerEncryptionApplier::class);
		$this->restorer = new FileRestorer(
			$this->fileMapper,
			new EntityWriter(),
			$this->lookup,
			$this->encryptionApplier,
		);
	}

	public function testPortablePathReencryptsTheFileRow(): void {
		$row = ['id' => 3, 'guid' => 'file-1', 'filename' => 'note.txt'];
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_PORTABLE),
			[BackupArchive::SECTION_FILES => [$row]],
		);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->never())->method('findFile');
		$this->encryptionApplier->expects($this->once())->method('fileRow')->with($row)->willReturn($row);
		$this->fileMapper->expects($this->never())->method('update');
		$this->fileMapper->expects($this->once())
			->method('insert')
			->with($this->callback(static fn(File $entity): bool => $entity->getGuid() === 'file-1'))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);

		$this->assertSame(1, $context->result->inserted[BackupArchive::SECTION_FILES]);
	}

	public function testMergeLooksUpGuidAndReplaceDoesNot(): void {
		$row = ['id' => 3, 'guid' => 'file-1'];
		$archive = BackupArchiveFactory::archive(
			BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_RAW),
			[BackupArchive::SECTION_FILES => [$row]],
		);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$existing = File::fromRow(['id' => 9, 'guid' => 'file-1']);

		$this->encryptionApplier->expects($this->never())->method('fileRow');
		$this->lookup->expects($this->once())->method('findFile')->with('file-1')->willReturn($existing);
		$this->fileMapper->expects($this->never())->method('insert');
		$this->fileMapper->expects($this->once())
			->method('update')
			->with($this->callback(static fn(File $entity): bool => $entity->getId() === 9))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);

		$this->assertSame(1, $context->result->updated[BackupArchive::SECTION_FILES]);
	}
}
