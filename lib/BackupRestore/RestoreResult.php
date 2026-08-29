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

/**
 * What a restore did, per {@see BackupArchive::SECTIONS} section: how many rows were deleted (replace mode), inserted,
 * updated (merge mode) and skipped, plus one warning message per skipped row.
 */
class RestoreResult {

	/** @var array<string, int> deleted rows per section */
	public array $deleted = [];

	/** @var array<string, int> newly inserted rows per section */
	public array $inserted = [];

	/** @var array<string, int> existing rows updated per section (merge mode) */
	public array $updated = [];

	/** @var array<string, int> rows of the artifact which could not be restored, per section */
	public array $skipped = [];

	/** @var string[] one message per skipped row */
	public array $warnings = [];

	public function __construct() {
		foreach (BackupArchive::SECTIONS as $section) {
			$this->deleted[$section] = 0;
			$this->inserted[$section] = 0;
			$this->updated[$section] = 0;
			$this->skipped[$section] = 0;
		}
	}

	public function countDeleted(string $section): void {
		$this->deleted[$section]++;
	}

	public function countInserted(string $section): void {
		$this->inserted[$section]++;
	}

	public function countUpdated(string $section): void {
		$this->updated[$section]++;
	}

	/**
	 * Records a row of the artifact which was not restored, together with the reason.
	 */
	public function skip(string $section, string $warning): void {
		$this->skipped[$section]++;
		$this->warnings[] = $warning;
	}

	public function totalDeleted(): int {
		return array_sum($this->deleted);
	}

	public function totalInserted(): int {
		return array_sum($this->inserted);
	}

	public function totalUpdated(): int {
		return array_sum($this->updated);
	}

	public function totalSkipped(): int {
		return array_sum($this->skipped);
	}
}
