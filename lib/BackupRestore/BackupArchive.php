<?php
/**
 * Nextcloud - passman
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

/**
 * In-memory representation of a Passman backup: the manifest plus one generic
 * {@see TableSnapshot} per table. Serialized to/from JSON by BackupSerializer.
 *
 * SECTIONS is listed in dependency order so restore can insert parents before
 * children (vaults -> credentials -> revisions/files/acl/share/delete).
 */
class BackupArchive {

	public const SECTION_VAULTS = 'vaults';
	public const SECTION_CREDENTIALS = 'credentials';
	public const SECTION_FILES = 'files';
	public const SECTION_REVISIONS = 'revisions';
	public const SECTION_SHARING_ACL = 'sharing_acl';
	public const SECTION_SHARE_REQUESTS = 'share_requests';
	public const SECTION_DELETE_VAULT_REQUESTS = 'delete_vault_requests';

	public const SECTIONS = [
		self::SECTION_VAULTS,
		self::SECTION_CREDENTIALS,
		self::SECTION_FILES,
		self::SECTION_REVISIONS,
		self::SECTION_SHARING_ACL,
		self::SECTION_SHARE_REQUESTS,
		self::SECTION_DELETE_VAULT_REQUESTS,
	];

	/** @var array<string, TableSnapshot> keyed by section name */
	public array $snapshots = [];

	public function __construct(
		public BackupManifest $manifest,
	) {
		foreach (self::SECTIONS as $section) {
			$this->snapshots[$section] = new TableSnapshot($section);
		}
	}

	/**
	 * Get the snapshot for a known section.
	 */
	public function section(string $section): TableSnapshot {
		return $this->snapshots[$section];
	}

	/**
	 * Recompute per-section counts and store them on the manifest.
	 *
	 * @return array<string, int>
	 */
	public function refreshCounts(): array {
		$counts = [];
		foreach (self::SECTIONS as $section) {
			$counts[$section] = $this->snapshots[$section]->count();
		}
		$this->manifest->counts = $counts;
		return $counts;
	}
}
