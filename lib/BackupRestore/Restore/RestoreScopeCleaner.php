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
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\BackupRestore\ScopeReader;
use OCA\Passman\BackupRestore\ScopeSelection;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\DeleteVaultRequestMapper;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\RestoreService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;

/**
 * Deletes the rows the manifest's scope is about, before a replace-mode restore re-inserts the artifact,
 * so that the insert is the only state left.
 *
 * Reads the scope via {@see ScopeReader}, then deletes over {@see BackupArchive::SECTIONS} in reverse:
 * children before parents, mirroring the dependency order the sections are inserted in.
 */
readonly class RestoreScopeCleaner {

	public function __construct(
		private ScopeReader              $scopeReader,
		private VaultMapper              $vaultMapper,
		private CredentialMapper         $credentialMapper,
		private FileMapper               $fileMapper,
		private CredentialRevisionMapper $revisionMapper,
		private SharingACLMapper         $sharingACLMapper,
		private ShareRequestMapper       $shareRequestMapper,
		private DeleteVaultRequestMapper $deleteVaultRequestMapper,
	) {
	}

	/**
	 * @throws \RuntimeException when the manifest targets a vault guid which is ambiguous on this instance
	 */
	public function clean(BackupManifest $manifest, RestoreResult $result): void {
		try {
			$scope = $this->scopeReader->forManifest($manifest);
		} catch (DoesNotExistException) {
			// the manifest's vault target does not exist on this instance: nothing to delete,
			// the insert below recreates it
			$scope = new ScopeSelection();
		} catch (MultipleObjectsReturnedException $e) {
			throw new \RuntimeException(sprintf(
				'This instance holds more than one %s, so the restore cannot tell which row to update. '
				. 'Resolve the duplicate manually or restore with --mode=%s.',
				'vault "' . ($manifest->targetVaultGuid ?? '?') . '"',
				RestoreService::MODE_REPLACE
			), 0, $e);
		}

		foreach (array_reverse(BackupArchive::SECTIONS) as $section) {
			$mapper = $this->mapperFor($section);
			foreach ($scope->entities($section) as $entity) {
				$mapper->delete($entity);
				$result->countDeleted($section);
			}
		}
	}

	private function mapperFor(string $section): QBMapper {
		return match ($section) {
			BackupArchive::SECTION_VAULTS => $this->vaultMapper,
			BackupArchive::SECTION_CREDENTIALS => $this->credentialMapper,
			BackupArchive::SECTION_FILES => $this->fileMapper,
			BackupArchive::SECTION_REVISIONS => $this->revisionMapper,
			BackupArchive::SECTION_SHARING_ACL => $this->sharingACLMapper,
			BackupArchive::SECTION_SHARE_REQUESTS => $this->shareRequestMapper,
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS => $this->deleteVaultRequestMapper,
		};
	}
}
