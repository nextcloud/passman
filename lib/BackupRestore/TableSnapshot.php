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
 * A generic snapshot of one Passman table: the section name plus its rows as
 * plain column => value maps.
 *
 * Rows are intentionally schema-agnostic so a single (de)serialization path
 * covers every table. Per-table specifics (e.g. the dual shape of a revision's
 * `credential_data` in portable vs raw mode, or stripping/re-applying the
 * server-side encryption layer) live in the Backup/Restore services, not here.
 */
class TableSnapshot {

	/**
	 * @param string $section one of BackupArchive::SECTION_*
	 * @param array<int, array<string, mixed>> $rows
	 */
	public function __construct(
		public string $section,
		public array $rows = [],
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public function addRow(array $row): void {
		$this->rows[] = $row;
	}

	public function count(): int {
		return count($this->rows);
	}
}
