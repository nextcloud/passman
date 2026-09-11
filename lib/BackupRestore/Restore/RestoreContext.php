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

use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Service\RestoreService;

/**
 * Mutable state of a single restore run:
 * the chosen mode, whether this is a dry-run, the result being built up, and the old-id -> this-instance-id maps,
 * that every section restorer feeds and consults, since every id of the artifact is regenerated on insert.
 *
 * A dry-run still fills those maps: inserts get a synthetic id from
 * {@see allocateDryRunId()} so child rows can remap without a database write.
 */
final class RestoreContext {

	/** @var array<int, int> old vault id => vault id on this instance */
	private array $vaultIds = [];

	/** @var array<int, int> old credential id => credential id on this instance */
	private array $credentialIds = [];

	private int $nextDryRunId = 0;

	public function __construct(
		private readonly string $mode,
		public readonly RestoreResult $result,
		private readonly bool $dryRun = false,
	) {
	}

	public function mode(): string {
		return $this->mode;
	}

	public function isMerge(): bool {
		return $this->mode === RestoreService::MODE_MERGE;
	}

	public function isDryRun(): bool {
		return $this->dryRun;
	}

	/**
	 * Next synthetic id for a dry-run insert, so child remaps still work without writing anything.
	 */
	public function allocateDryRunId(): int {
		return ++$this->nextDryRunId;
	}

	public function rememberVault(?int $oldId, int $newId): void {
		if ($oldId !== null) {
			$this->vaultIds[$oldId] = $newId;
		}
	}

	public function rememberCredential(?int $oldId, int $newId): void {
		if ($oldId !== null) {
			$this->credentialIds[$oldId] = $newId;
		}
	}

	public function vaultId(mixed $oldId): ?int {
		return $this->mapId($this->vaultIds, $oldId);
	}

	public function credentialId(mixed $oldId): ?int {
		return $this->mapId($this->credentialIds, $oldId);
	}

	/**
	 * @param array<int, int> $map old id => id on this instance
	 */
	private function mapId(array $map, mixed $oldId): ?int {
		if ($oldId === null || $oldId === '') {
			return null;
		}
		return $map[(int)$oldId] ?? null;
	}
}
