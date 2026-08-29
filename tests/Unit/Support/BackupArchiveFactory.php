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

namespace OCA\Passman\Tests\Unit\Support;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\BackupSerializer;

/**
 * Builds in-memory backup archives for unit tests, without leftover live dumps.
 */
final class BackupArchiveFactory {

	/**
	 * @param array<string, int> $counts
	 */
	public static function manifest(
		int     $formatVersion = BackupManifest::FORMAT_VERSION,
		string  $appVersion = '2.6.1',
		int     $createdAt = 0,
		string  $scope = BackupManifest::SCOPE_INSTANCE,
		string  $encryptionMode = BackupManifest::MODE_PORTABLE,
		string  $instanceId = 'test-instance',
		?string $targetUserId = null,
		?string $targetVaultGuid = null,
		array   $counts = [],
		?string $checksum = 'test-checksum',
	): BackupManifest {
		return new BackupManifest(
			$formatVersion,
			$appVersion,
			$createdAt,
			$scope,
			$encryptionMode,
			$instanceId,
			$targetUserId,
			$targetVaultGuid,
			$counts,
			$checksum,
		);
	}

	/**
	 * @param array<string, list<array<string, mixed>>> $sectionRows
	 */
	public static function archive(?BackupManifest $manifest = null, array $sectionRows = []): BackupArchive {
		$archive = new BackupArchive($manifest ?? self::manifest());
		foreach ($sectionRows as $section => $rows) {
			foreach ($rows as $row) {
				$archive->section($section)->addRow($row);
			}
		}
		return $archive;
	}

	public static function encode(BackupArchive $archive, bool $pretty = true): string {
		return (new BackupSerializer())->encode($archive);
	}
}
