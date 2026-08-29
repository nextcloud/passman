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
 * Reads a single column of a raw backup row (an associative array as decoded from
 * the artifact JSON), tolerating the missing/empty/malformed values a foreign or
 * hand-edited artifact may hold.
 */
final class BackupRow {

	/**
	 * @param array<string, mixed> $row
	 */
	public static function readString(array $row, string $column): ?string {
		$value = $row[$column] ?? null;
		if ($value === null || $value === '' || is_array($value)) {
			return null;
		}
		return (string)$value;
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function readInt(array $row, string $column): ?int {
		$value = $row[$column] ?? null;
		if ($value === null || $value === '' || !is_numeric($value)) {
			return null;
		}
		return (int)$value;
	}
}
