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

use OCA\Passman\Exception\InvalidBackupException;

/**
 * Converts a BackupArchive to and from the on-disk JSON artifact format.
 *
 * Artifact shape: { "manifest": {...}, "sections": ["<section>": [ {col: value, ...}, ... ], ...] }
 * A single generic path handles every section via {@see BackupArchive::SECTIONS}.
 */
class BackupSerializer {

	private const JSON_DEPTH = 512;
	public const JSON_ENCODE_DEFAULT_FLAGS = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	/**
	 * Serialize an archive to the JSON artifact string.
	 *
	 * @throws InvalidBackupException on encoding failure
	 */
	public function encode(BackupArchive $archive, bool $pretty = true): string {
		$archive->refreshCounts();
		$archive->manifest->calculateArchiveChecksum($archive);

		$data = [
			'manifest' => $archive->manifest->toArray(),
			'sections' => $archive->sectionsToArray(),
		];

		try {
			$flags = self::JSON_ENCODE_DEFAULT_FLAGS;
			if ($pretty) {
				$flags |= JSON_PRETTY_PRINT;
			}
			return json_encode($data, $flags);
		} catch (\JsonException $e) {
			throw new InvalidBackupException('Failed to encode backup artifact: ' . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * Parse and validate a JSON artifact string into an archive.
	 *
	 * @throws InvalidBackupException on invalid JSON, an unsupported/malformed manifest or if the manifest checksum does not match the content
	 */
	public function decode(string $json): BackupArchive {
		try {
			$data = json_decode($json, true, self::JSON_DEPTH, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			throw new InvalidBackupException('Backup artifact is not valid JSON: ' . $e->getMessage(), 0, $e);
		}

		if (
			!is_array($data) ||
			!isset($data['manifest']) ||
			!isset($data['sections']) ||
			!is_array($data['manifest']) ||
			!is_array($data['sections'])
		) {
			throw new InvalidBackupException('Backup artifact is missing its manifest');
		}

		$archive = new BackupArchive(BackupManifest::fromArray($data['manifest']));
		foreach (BackupArchive::SECTIONS as $section) {
			$archive->snapshots[$section] = new TableSnapshot($section, $this->readRows($section, $data['sections'][$section] ?? []));
		}

		if (!$archive->manifest->validateArchiveChecksum($archive)) {
			throw new InvalidBackupException(
				'The manifest and content of the backup artifact do not match its checksum. '
				. 'The artifact was modified or is incomplete.'
			);
		}

		return $archive;
	}

	/**
	 * @param mixed $rows expected to be a list of row maps
	 * @return array<int, array<string, mixed>>
	 * @throws InvalidBackupException when the section is not a list of row maps
	 */
	private function readRows(string $section, mixed $rows): array {
		if (!is_array($rows)) {
			throw new InvalidBackupException(sprintf('Backup section "%s" must be a list of rows', $section));
		}
		$result = [];
		foreach ($rows as $row) {
			if (!is_array($row)) {
				throw new InvalidBackupException(sprintf('Backup section "%s" contains a non-object row', $section));
			}
			$result[] = $row;
		}
		return $result;
	}
}
