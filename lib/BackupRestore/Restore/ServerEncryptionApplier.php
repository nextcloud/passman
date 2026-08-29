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
use OCA\Passman\BackupRestore\BackupRow;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Service\EncryptService;

/**
 * Re-applies the Nextcloud server side encryption layer of this instance to a portable artifact's
 * credential/file/revision rows.
 * The mirror image of the decrypt step that the BackupService performs when exporting a portable artifact.
 *
 * Fields the backup could not decrypt because they held no encrypted value are restored as NULL,
 * instead of an encrypted empty string.
 */
readonly class ServerEncryptionApplier {

	public function __construct(
		private EncryptService $encryptService,
	) {
	}

	/**
	 * Reapplies the server side encryption to the protected fields of the given credential data.
	 * @param array<string, mixed> $credential
	 * @return array<string, mixed>
	 * @throws InvalidBackupException when the credential cannot be encrypted
	 */
	public function credentialRow(array $credential): array {
		return $this->encryptFields(
			$credential,
			$this->encryptService->encrypted_credential_fields,
			fn(array $data): array => (array)$this->encryptService->encryptCredential($data),
			'credential "' . (BackupRow::readString($credential, 'guid') ?? '?') . '"'
		);
	}

	/**
	 * Reapplies the server side encryption to the protected fields of the given file data.
	 * @param array<string, mixed> $file
	 * @return array<string, mixed>
	 * @throws InvalidBackupException when the file cannot be encrypted
	 */
	public function fileRow(array $file): array {
		return $this->encryptFields(
			$file,
			['filename', 'file_data'],
			fn(array $data): array => (array)$this->encryptService->encryptFile($data),
			'file "' . (BackupRow::readString($file, 'guid') ?? '?') . '"'
		);
	}

	/**
	 * A revision stores a whole credential as base64(json(...)).
	 * A portable artifact holds it as a decrypted array, so the server side encryption layer
	 * and the encoding are re-applied here.
	 *
	 * @param mixed $credential the decrypted credential array of a portable revision
	 * @return string base64(json(<server encrypted credential>)) as the column stores it
	 * @throws InvalidBackupException when the revision is malformed or cannot be encrypted
	 */
	public function revisionData(mixed $credential, ?string $revisionGuid): string {
		if (!is_array($credential)) {
			throw new InvalidBackupException(sprintf(
				'The credential data of the revision "%s" is not an object. A %s backup has to hold the decrypted credential of a revision.',
				$revisionGuid ?? '?',
				BackupManifest::MODE_PORTABLE
			));
		}

		$credential = $this->credentialRow($credential);

		try {
			return base64_encode(json_encode($credential, JSON_THROW_ON_ERROR));
		} catch (\JsonException $e) {
			throw new InvalidBackupException(
				'Could not encode the credential data of the revision "' . ($revisionGuid ?? '?') . '": ' . $e->getMessage(), 0, $e
			);
		}
	}

	/**
	 * @param array<string, mixed> $row
	 * @param string[] $fields the server side encrypted fields of the row
	 * @param callable(array<string, mixed>): array<string, mixed> $encrypt
	 * @return array<string, mixed>
	 * @throws InvalidBackupException when the row cannot be encrypted
	 */
	private function encryptFields(array $row, array $fields, callable $encrypt, string $subject): array {
		// EncryptService needs a string for every field, an empty field of the artifact stays empty
		$emptyFields = [];
		foreach ($fields as $field) {
			$value = $row[$field] ?? null;
			if ($value === null || $value === false) {
				$emptyFields[] = $field;
				$row[$field] = '';
			}
		}

		try {
			$row = $encrypt($row);
		} catch (\Throwable $e) {
			throw new InvalidBackupException('Could not encrypt ' . $subject . ' of the backup: ' . $e->getMessage(), 0, $e);
		}

		foreach ($emptyFields as $field) {
			$row[$field] = null;
		}
		return $row;
	}
}
