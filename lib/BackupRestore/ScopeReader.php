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

namespace OCA\Passman\BackupRestore;

use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\DeleteVaultRequestMapper;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

/**
 * Reads the rows of an instance/user/vault scope, shared by backup (export) and
 * replace-mode restore (which deletes the same rows before re-inserting the artifact).
 *
 * The vault scope never includes file rows: credential to file links live in the e2e
 * encrypted "files" column and cannot be resolved on the server, so file rows are
 * not bound to a vault.
 */
readonly class ScopeReader {

	public function __construct(
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
	 * @param string $scope one of BackupManifest::SCOPE_*
	 * @throws DoesNotExistException when the vault scope target does not exist
	 * @throws MultipleObjectsReturnedException when the vault scope target's guid is ambiguous on this instance
	 */
	public function read(string $scope, ?string $userId, ?string $vaultGuid): ScopeSelection {
		return match ($scope) {
			BackupManifest::SCOPE_USER => $this->readUser((string)$userId),
			BackupManifest::SCOPE_VAULT => $this->readVault((string)$vaultGuid),
			default => $this->readInstance(),
		};
	}

	/**
	 * @throws DoesNotExistException when the manifest's vault target does not exist
	 * @throws MultipleObjectsReturnedException when the manifest's vault target's guid is ambiguous on this instance
	 */
	public function forManifest(BackupManifest $manifest): ScopeSelection {
		return $this->read($manifest->scope, $manifest->targetUserId, $manifest->targetVaultGuid);
	}

	private function readInstance(): ScopeSelection {
		return new ScopeSelection(
			vaults: $this->vaultMapper->getAllVaults(),
			credentials: $this->credentialMapper->getAll(),
			files: $this->fileMapper->getAllFiles(),
			revisions: $this->revisionMapper->getAll(),
			sharingAcl: $this->sharingACLMapper->getAll(),
			shareRequests: $this->shareRequestMapper->getAll(),
			deleteVaultRequests: $this->deleteVaultRequestMapper->getDeleteRequests(),
		);
	}

	private function readUser(string $userId): ScopeSelection {
		$vaults = $this->vaultMapper->findVaultsFromUser($userId);
		$credentials = $this->credentialMapper->getByUser($userId);
		$sharing = $this->readSharingRows($vaults, $credentials, $userId);

		return new ScopeSelection(
			vaults: $vaults,
			credentials: $credentials,
			files: $this->fileMapper->getFilesByUser($userId),
			revisions: $this->revisionMapper->getByUser($userId),
			sharingAcl: $sharing['acl'],
			shareRequests: $sharing['shareRequests'],
			deleteVaultRequests: $sharing['deleteVaultRequests'],
		);
	}

	/**
	 * @throws DoesNotExistException when the vault does not exist
	 * @throws MultipleObjectsReturnedException when the vault guid is ambiguous on this instance
	 */
	private function readVault(string $vaultGuid): ScopeSelection {
		$vault = $this->vaultMapper->getByGuid($vaultGuid);
		$credentials = $this->credentialMapper->getCredentialsByVaultId((string)$vault->getId(), $vault->getUserId());
		$sharing = $this->readSharingRows([$vault], $credentials, null);

		/** @var CredentialRevision[] $revisionLists */
		$revisionLists = [];
		foreach ($credentials as $credential) {
			$revisionLists[] = $this->revisionMapper->getRevisions($credential->getId());
		}

		return new ScopeSelection(
			vaults: [$vault],
			credentials: $credentials,
			files: [],
			revisions: ScopeSelection::uniqueById($revisionLists),
			sharingAcl: $sharing['acl'],
			shareRequests: $sharing['shareRequests'],
			deleteVaultRequests: $sharing['deleteVaultRequests'],
		);
	}

	/**
	 * Collects the sharing rows referencing one of the given vaults or credentials,
	 * plus the rows referencing the user itself for a user scope.
	 *
	 * @param Vault[] $vaults
	 * @param Credential[] $credentials
	 * @return array{acl: array, shareRequests: array, deleteVaultRequests: array}
	 */
	private function readSharingRows(array $vaults, array $credentials, ?string $userId): array {
		$acl = [];
		$shareRequests = [];
		$deleteRequests = [];

		if ($userId !== null) {
			$acl[] = $this->sharingACLMapper->getByUser($userId);
			$shareRequests[] = $this->shareRequestMapper->getByUser($userId);
			$deleteRequests[] = $this->deleteVaultRequestMapper->getByRequestedBy($userId);
		}

		foreach ($vaults as $vault) {
			$acl[] = $this->sharingACLMapper->getByVaultGuid($vault->getGuid());
			$shareRequests[] = $this->shareRequestMapper->getByTargetVaultGuid($vault->getGuid());
			$deleteRequests[] = $this->deleteVaultRequestMapper->getByVaultGuid($vault->getGuid());
		}

		foreach ($credentials as $credential) {
			$acl[] = $this->sharingACLMapper->getCredentialAclList($credential->getGuid());
			$shareRequests[] = $this->shareRequestMapper->getShareRequestsByItemGuid($credential->getGuid());
		}

		return [
			'acl' => ScopeSelection::uniqueById($acl),
			'shareRequests' => ScopeSelection::uniqueById($shareRequests),
			'deleteVaultRequests' => ScopeSelection::uniqueById($deleteRequests),
		];
	}
}
