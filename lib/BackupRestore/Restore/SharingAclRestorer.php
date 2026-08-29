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
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;

readonly class SharingAclRestorer implements SectionRestorer {

	public function __construct(
		private SharingACLMapper $sharingACLMapper,
		private EntityWriter     $entityWriter,
		private RestoreLookup    $lookup,
	) {
	}

	public function section(): string {
		return BackupArchive::SECTION_SHARING_ACL;
	}

	public function restore(BackupArchive $archive, RestoreContext $context): void {
		$section = $this->section();

		foreach ($archive->section($section)->rows as $row) {
			$itemGuid = BackupRow::readString($row, 'item_guid');
			$itemId = $this->lookup->resolveCredentialId($context, $row['item_id'] ?? null, $itemGuid);
			if ($itemId === null) {
				$context->result->skip($section, RestoreWarnings::missingCredential('sharing acl entry', $itemGuid));
				continue;
			}
			$row['item_id'] = $itemId;

			$vaultGuid = BackupRow::readString($row, 'vault_guid');
			if ($vaultGuid !== null || BackupRow::readInt($row, 'vault_id') !== null) {
				$vaultId = $this->lookup->resolveVaultId($context, $row['vault_id'] ?? null, $vaultGuid);
				if ($vaultId === null) {
					$context->result->skip($section, RestoreWarnings::missingVault('sharing acl entry', $itemGuid, $vaultGuid));
					continue;
				}
				$row['vault_id'] = $vaultId;
			}

			$existingId = $context->isMerge()
				? $this->lookup->findExisting(
					fn() => $this->sharingACLMapper->getItemACL(BackupRow::readString($row, 'user_id'), (string)$itemGuid),
					'sharing acl entry of the credential "' . $itemGuid . '"'
				)?->getId()
				: null;
			$this->entityWriter->store($section, $this->sharingACLMapper, SharingACL::class, $row, $existingId, $context->result);
		}
	}
}
