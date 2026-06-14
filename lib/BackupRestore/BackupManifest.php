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

	/**
	 * @param array<string, int> $counts Number of rows per section (informational).
	 */
	public function __construct(
		public int $formatVersion,
		public string $appVersion,
		public int $createdAt,
		public string $scope,
		public string $encryptionMode,
		public string $instanceId,
		public ?string $targetUserId = null,
		public ?string $targetVaultGuid = null,
		public array $counts = [],
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
			'format_version' => $this->formatVersion,
			'app_version' => $this->appVersion,
			'created_at' => $this->createdAt,
			'scope' => $this->scope,
			'encryption_mode' => $this->encryptionMode,
			'instance_id' => $this->instanceId,
			'target_user_id' => $this->targetUserId,
			'target_vault_guid' => $this->targetVaultGuid,
			'counts' => $this->counts,
		];
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
		);
	}
}
