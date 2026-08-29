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

namespace OCA\Passman\BackupRestore\Restore;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupRow;
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Exception\InvalidBackupException;

/**
 * @throws InvalidBackupException when a portable file cannot be encrypted
 */
readonly class FileRestorer implements SectionRestorer {

	public function __construct(
		private FileMapper              $fileMapper,
		private EntityWriter            $entityWriter,
		private RestoreLookup           $lookup,
		private ServerEncryptionApplier $encryptionApplier,
	) {
	}

	public function section(): string {
		return BackupArchive::SECTION_FILES;
	}

	public function restore(BackupArchive $archive, RestoreContext $context): void {
		$section = $this->section();

		foreach ($archive->section($section)->rows as $row) {
			if ($archive->manifest->isPortable()) {
				$row = $this->encryptionApplier->fileRow($row);
			}

			$existingId = $context->isMerge() ? $this->lookup->findFile(BackupRow::readString($row, 'guid'))?->getId() : null;
			$this->entityWriter->store($section, $this->fileMapper, File::class, $row, $existingId, $context->result);
		}
	}
}
