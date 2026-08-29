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

use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\RestoreService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

/**
 * Guid lookups of rows already present on this instance.
 * Used by merge mode to find the row a snapshot row should update,
 * and to resolve the vault/credential a sharing row points to when the artifact scope did not include it.
 */
readonly class RestoreLookup {

	public function __construct(
		private VaultMapper              $vaultMapper,
		private CredentialMapper         $credentialMapper,
		private FileMapper               $fileMapper,
		private CredentialRevisionMapper $revisionMapper,
	) {
	}

	/**
	 * @throws \RuntimeException when the guid exists more than once on this instance
	 */
	public function findVault(?string $guid): ?Vault {
		return $guid === null ? null : $this->findExisting(
			fn() => $this->vaultMapper->getByGuid($guid),
			'vault "' . $guid . '"'
		);
	}

	/**
	 * @throws \RuntimeException when the guid exists more than once on this instance
	 */
	public function findCredential(?string $guid): ?Credential {
		return $guid === null ? null : $this->findExisting(
			fn() => $this->credentialMapper->getCredentialByGUID($guid),
			'credential "' . $guid . '"'
		);
	}

	/**
	 * @throws \RuntimeException when the guid exists more than once on this instance
	 */
	public function findFile(?string $guid): ?File {
		return $guid === null ? null : $this->findExisting(
			fn() => $this->fileMapper->getFileByGuid($guid),
			'file "' . $guid . '"'
		);
	}

	/**
	 * @return array<string, int> guid => revision id of the given credential
	 */
	public function revisionIdsByGuid(int $credentialId): array {
		$revisions = [];
		foreach ($this->revisionMapper->getRevisions($credentialId) as $revision) {
			$revisions[$revision->getGuid()] = $revision->getId();
		}
		return $revisions;
	}

	/**
	 * Looks up the row a snapshot row should update in merge mode.
	 *
	 * @template T of Entity
	 * @param callable(): T $lookup
	 * @param string $subject what is looked up, for the ambiguity message
	 * @return T|null null when this instance holds no such row
	 * @throws \RuntimeException when more than one row of this instance matches
	 */
	public function findExisting(callable $lookup, string $subject): ?Entity {
		try {
			return $lookup();
		} catch (DoesNotExistException) {
			return null;
		} catch (MultipleObjectsReturnedException $e) {
			throw new \RuntimeException(sprintf(
				'This instance holds more than one %s, so the restore cannot tell which row to update. '
				. 'Resolve the duplicate manually or restore with --mode=%s.',
				$subject,
				RestoreService::MODE_REPLACE
			), 0, $e);
		}
	}

	/**
	 * Resolves the credential a sharing row points to, either via the artifact or,
	 * for a merge into existing data, via its guid on this instance.
	 *
	 * @return int|null null when the credential is unknown here
	 * @throws \RuntimeException when the guid is ambiguous on this instance
	 */
	public function resolveCredentialId(RestoreContext $context, mixed $oldId, ?string $guid): ?int {
		$id = $context->credentialId($oldId);
		if ($id !== null) {
			return $id;
		}

		$credential = $this->findCredential($guid);
		return $credential?->getId();
	}

	/**
	 * @return int|null null when the vault is unknown here
	 * @throws \RuntimeException when the guid is ambiguous on this instance
	 */
	public function resolveVaultId(RestoreContext $context, mixed $oldId, ?string $guid): ?int {
		$id = $context->vaultId($oldId);
		if ($id !== null) {
			return $id;
		}

		$vault = $this->findVault($guid);
		return $vault?->getId();
	}
}
