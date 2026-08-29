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

use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Service\RestoreService;
use OCP\IConfig;

/**
 * Whether an artifact can be applied to this instance at all.
 * The limitations of restoring it which the caller should present to the admin.
 */
readonly class RestorePreflight {

	public function __construct(
		private IConfig $config,
	) {
	}

	/**
	 * @throws InvalidBackupException when the artifact cannot be applied to this instance
	 */
	public function assertRestorable(BackupManifest $manifest, bool $force): void {
		if ($manifest->scope === BackupManifest::SCOPE_USER && empty($manifest->targetUserId)) {
			throw new InvalidBackupException('The manifest has the "' . BackupManifest::SCOPE_USER . '" scope but no target user id');
		}
		if ($manifest->scope === BackupManifest::SCOPE_VAULT && empty($manifest->targetVaultGuid)) {
			throw new InvalidBackupException('The manifest has the "' . BackupManifest::SCOPE_VAULT . '" scope but no target vault guid');
		}

		$instanceId = $this->config->getSystemValueString('instanceid', '');
		if (!$manifest->isPortable() && !$force && $manifest->instanceId !== $instanceId) {
			throw new InvalidBackupException(sprintf(
				'This is a %s backup of the instance "%s", but this instance is "%s". Its server side encrypted columns cannot be '
				. 'decrypted here, so the restored data would be unreadable. Restore a %s backup instead or pass --force to '
				. 'insert the rows verbatim anyway.',
				BackupManifest::MODE_RAW,
				$manifest->instanceId,
				$instanceId,
				BackupManifest::MODE_PORTABLE
			));
		}
	}

	/**
	 * Limitations of restoring the given artifact which the caller should present to the admin.
	 *
	 * @param string $mode one of RestoreService::MODE_*
	 * @return string[]
	 */
	public function caveats(BackupManifest $manifest, string $mode): array {
		$caveats = [];

		if ($mode === RestoreService::MODE_MERGE) {
			$caveats[] = 'Merge only touches rows of the artifact: rows which exist on this instance but not in the artifact are kept.';
		}
		if ($manifest->scope !== BackupManifest::SCOPE_INSTANCE) {
			$caveats[] = 'Sharing rows of the artifact pointing to a vault or credential which is neither part of the artifact nor '
				. 'present on this instance are skipped.';
		}
		if ($manifest->isPortable()) {
			$caveats[] = 'The server side encryption layer is re-applied with the key material of this instance '
				. '(like secret and passwordsalt of config.php), the restored rows are bound to it from now on.';
		}
		$caveats[] = 'Vault, credential, file and revision GUIDs are preserved, their numeric ids are regenerated. '
			. 'Clients have to sync again to pick up the restored state.';

		return $caveats;
	}
}
