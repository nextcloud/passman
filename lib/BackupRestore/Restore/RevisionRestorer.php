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
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Exception\InvalidBackupException;
use OCP\DB\Exception;

/**
 * A revision stores a whole credential as base64(json(...)).
 * A portable artifact holds it as a decrypted array, so the server side encryption layer and the encoding
 * are re-applied here via {@see ServerEncryptionApplier::revisionData()}.
 */
readonly class RevisionRestorer implements SectionRestorer {

	public function __construct(
		private CredentialRevisionMapper $revisionMapper,
		private EntityWriter             $entityWriter,
		private RestoreLookup            $lookup,
		private ServerEncryptionApplier  $encryptionApplier,
	) {
	}

	public function section(): string {
		return BackupArchive::SECTION_REVISIONS;
	}

	/**
	 * @throws InvalidBackupException on a malformed portable revision
	 * @throws Exception
	 */
	public function restore(BackupArchive $archive, RestoreContext $context): void {
		$section = $this->section();
		/** @var array<int, array<string, int>> $existingRevisions guid => revision id, per credential id */
		$existingRevisions = [];

		foreach ($archive->section($section)->rows as $row) {
			$newCredentialId = $context->credentialId($row['credential_id'] ?? null);
			if ($newCredentialId === null) {
				$context->result->skip($section, RestoreWarnings::credentialOfRevisionMissing(
					BackupRow::readString($row, 'guid'),
					BackupRow::readInt($row, 'credential_id')
				));
				continue;
			}
			$row['credential_id'] = $newCredentialId;

			if ($archive->manifest->isPortable()) {
				$row['credential_data'] = $this->encryptionApplier->revisionData(
					$row['credential_data'] ?? null,
					BackupRow::readString($row, 'guid')
				);
			}

			$existingId = null;
			if ($context->isMerge()) {
				$existingRevisions[$newCredentialId] ??= $this->lookup->revisionIdsByGuid($newCredentialId);
				$existingId = $existingRevisions[$newCredentialId][BackupRow::readString($row, 'guid') ?? ''] ?? null;
			}

			$this->entityWriter->store($section, $this->revisionMapper, CredentialRevision::class, $row, $existingId, $context->result);
		}
	}
}
