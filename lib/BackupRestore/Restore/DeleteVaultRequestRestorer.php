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
use OCA\Passman\Db\DeleteVaultRequest;
use OCA\Passman\Db\DeleteVaultRequestMapper;

readonly class DeleteVaultRequestRestorer implements SectionRestorer {

	public function __construct(
		private DeleteVaultRequestMapper $deleteVaultRequestMapper,
		private EntityWriter             $entityWriter,
		private RestoreLookup            $lookup,
	) {
	}

	public function section(): string {
		return BackupArchive::SECTION_DELETE_VAULT_REQUESTS;
	}

	public function restore(BackupArchive $archive, RestoreContext $context): void {
		$section = $this->section();

		foreach ($archive->section($section)->rows as $row) {
			$vaultGuid = BackupRow::readString($row, 'vault_guid');
			if (empty($vaultGuid)) {
				// should not happen, but just in case, let's just ignore the broken delete vault request row
				continue;
			}
			$existingId = $context->isMerge()
				? $this->lookup->findExisting(
					fn() => $this->deleteVaultRequestMapper->getDeleteRequestsForVault($vaultGuid),
					'delete request of the vault "' . $vaultGuid . '"'
				)?->getId()
				: null;
			$this->entityWriter->store($section, $this->deleteVaultRequestMapper, DeleteVaultRequest::class, $row, $existingId, $context->result);
		}
	}
}
