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
 * Metadata header of a Passman backup artifact. Describes how the artifact was
 * produced (scope + encryption mode) so that restore can interpret it correctly.
 */
class BackupManifest {

	public const FORMAT_VERSION = 1;

	public const SCOPE_INSTANCE = 'instance';
	public const SCOPE_USER = 'user';
	public const SCOPE_VAULT = 'vault';
	public const SCOPES = [self::SCOPE_INSTANCE, self::SCOPE_USER, self::SCOPE_VAULT];

	public const MODE_PORTABLE = 'portable';
	public const MODE_RAW = 'raw';
	public const MODES = [self::MODE_PORTABLE, self::MODE_RAW];

	public const CHECKSUM_ALGORITHM = 'sha256';

	/** The one manifest field which cannot be covered by the checksum: the checksum itself. */
	private const CHECKSUM_FIELD = 'checksum';

	/**
	 * @param array<string, int> $counts Number of rows per section (informational).
	 * @param string|null $checksum Digest over every other field of this manifest plus the archive sections.
	 */
	public function __construct(
		public int     $formatVersion,
		public string  $appVersion,
		public int     $createdAt,
		public string  $scope,
		public string  $encryptionMode,
		public string  $instanceId,
		public ?string $targetUserId = null,
		public ?string $targetVaultGuid = null,
		public array   $counts = [],
		public ?string $checksum = null,
	) {
	}

	public static function isValidScope(string $scope): bool {
		return in_array($scope, self::SCOPES, true);
	}

	public static function isValidMode(string $mode): bool {
		return in_array($mode, self::MODES, true);
	}

	public function isPortable(): bool {
		return $this->encryptionMode === self::MODE_PORTABLE;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array {
		return [
			'format_version'     => $this->formatVersion,
			'app_version'        => $this->appVersion,
			'created_at'         => $this->createdAt,
			'scope'              => $this->scope,
			'encryption_mode'    => $this->encryptionMode,
			'instance_id'        => $this->instanceId,
			'target_user_id'     => $this->targetUserId,
			'target_vault_guid'  => $this->targetVaultGuid,
			'counts'             => $this->counts,
			self::CHECKSUM_FIELD => $this->checksum,
		];
	}

	/**
	 * Adds a checksum to this manifest about its own fields and the given archive content.
	 *
	 * This is an integrity check against truncated or edited artifacts, not a signature:
	 * a portable artifact has to stay verifiable on a foreign instance, which cannot know a key of the instance that wrote it.
	 *
	 * May add a signature of this checksum later, if someone requests it.
	 *
	 * @param BackupArchive $backupArchive
	 * @return void
	 * @throws InvalidBackupException when the manifest or a section cannot be encoded
	 */
	public function calculateArchiveChecksum(BackupArchive $backupArchive): void {
		$this->checksum = $this->computeArchiveChecksum($backupArchive);
	}

	/**
	 * Whether this manifest and the sections of the given archive still match the stored checksum.
	 *
	 * @throws InvalidBackupException when the manifest or a section cannot be encoded
	 */
	public function validateArchiveChecksum(BackupArchive $backupArchive): bool {
		return !empty($this->checksum) && hash_equals($this->checksum, $this->computeArchiveChecksum($backupArchive));
	}

	/**
	 * Digest over every field of this manifest except the checksum itself, followed by the
	 * content of every section in {@see BackupArchive::SECTIONS} order.
	 *
	 * The manifest is hashed in the canonical field order and types of {@see self::toArray()},
	 * so reformatting or reordering the stored manifest keys does not invalidate an artifact,
	 * while changing any value does.
	 *
	 * @throws InvalidBackupException when the manifest or a section cannot be encoded
	 */
	private function computeArchiveChecksum(BackupArchive $backupArchive): string {
		$fields = $this->toArray();
		unset($fields[self::CHECKSUM_FIELD]);

		$context = hash_init(self::CHECKSUM_ALGORITHM);
		hash_update($context, 'manifest');
		hash_update($context, self::encodeForChecksum('manifest', $fields));
		// sections are encoded and hashed one by one to avoid excessive memory usage
		foreach ($backupArchive->sectionsToArray() as $section => $rows) {
			hash_update($context, $section);
			hash_update($context, self::encodeForChecksum($section, $rows));
		}
		return hash_final($context);
	}

	/**
	 * @param array<string|int, mixed> $data
	 * @throws InvalidBackupException when the data cannot be encoded
	 */
	private static function encodeForChecksum(string $subject, array $data): string {
		try {
			return json_encode($data, BackupSerializer::JSON_ENCODE_DEFAULT_FLAGS);
		} catch (\JsonException $e) {
			throw new InvalidBackupException(
				'Failed to encode the "' . $subject . '" part of the backup for checksumming: ' . $e->getMessage(), 0, $e
			);
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @throws InvalidBackupException when the manifest is unsupported or malformed
	 */
	public static function fromArray(array $data): self {
		$formatVersion = isset($data['format_version']) ? (int)$data['format_version'] : 0;
		if ($formatVersion !== self::FORMAT_VERSION) {
			throw new InvalidBackupException(sprintf(
				'Unsupported backup format version "%s" (expected %d)',
				$data['format_version'] ?? 'null',
				self::FORMAT_VERSION
			));
		}

		$scope = isset($data['scope']) ? (string)$data['scope'] : '';
		if (!self::isValidScope($scope)) {
			throw new InvalidBackupException('Unknown backup scope: "' . $scope . '"');
		}

		$mode = isset($data['encryption_mode']) ? (string)$data['encryption_mode'] : '';
		if (!self::isValidMode($mode)) {
			throw new InvalidBackupException('Unknown encryption mode: "' . $mode . '"');
		}

		$checksum = isset($data[self::CHECKSUM_FIELD]) ? (string)$data[self::CHECKSUM_FIELD] : null;
		if (empty($checksum)) {
			throw new InvalidBackupException('The manifest is missing its checksum');
		}

		return new self(
			$formatVersion,
			isset($data['app_version']) ? (string)$data['app_version'] : '',
			isset($data['created_at']) ? (int)$data['created_at'] : 0,
			$scope,
			$mode,
			isset($data['instance_id']) ? (string)$data['instance_id'] : '',
			isset($data['target_user_id']) ? (string)$data['target_user_id'] : null,
			isset($data['target_vault_guid']) ? (string)$data['target_vault_guid'] : null,
			isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : [],
			$checksum,
		);
	}
}
