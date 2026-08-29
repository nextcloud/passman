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
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\DeleteVaultRequest;
use OCA\Passman\Db\File;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\Vault;
use OCP\AppFramework\Db\Entity;

/**
 * The rows of one backup/restore scope (instance, user or vault), one list per
 * {@see BackupArchive::SECTION_*}. Produced by {@see ScopeReader}.
 */
final readonly class ScopeSelection {

	/**
	 * @param Vault[] $vaults
	 * @param Credential[] $credentials
	 * @param File[] $files
	 * @param CredentialRevision[] $revisions
	 * @param SharingACL[] $sharingAcl
	 * @param ShareRequest[] $shareRequests
	 * @param DeleteVaultRequest[] $deleteVaultRequests
	 */
	public function __construct(
		public array $vaults = [],
		public array $credentials = [],
		public array $files = [],
		public array $revisions = [],
		public array $sharingAcl = [],
		public array $shareRequests = [],
		public array $deleteVaultRequests = [],
	) {
	}

	/**
	 * @param string $section one of BackupArchive::SECTION_*
	 * @return Entity[]
	 */
	public function entities(string $section): array {
		return match ($section) {
			BackupArchive::SECTION_VAULTS => $this->vaults,
			BackupArchive::SECTION_CREDENTIALS => $this->credentials,
			BackupArchive::SECTION_FILES => $this->files,
			BackupArchive::SECTION_REVISIONS => $this->revisions,
			BackupArchive::SECTION_SHARING_ACL => $this->sharingAcl,
			BackupArchive::SECTION_SHARE_REQUESTS => $this->shareRequests,
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS => $this->deleteVaultRequests,
			default => throw new \InvalidArgumentException('Unknown backup section: "' . $section . '"'),
		};
	}

	/**
	 * Flattens the results of overlapping scope queries, keeping every row once.
	 *
	 * @template T of Entity
	 * @param array<int, T[]> $entityLists
	 * @return T[]
	 */
	public static function uniqueById(array $entityLists): array {
		$unique = [];
		foreach (array_merge([], ...$entityLists) as $entity) {
			$unique[$entity->getId()] = $entity;
		}
		return array_values($unique);
	}
}
