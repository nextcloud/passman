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

namespace OCA\Passman\Service;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\ScopeReader;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\File;
use OCA\Passman\Utility\Utils;
use OCP\App\IAppManager;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IConfig;

/**
 * Exports Passman data into an in-memory {@see BackupArchive}.
 *
 * The scope decides which rows are read (via the shared {@see ScopeReader}), the
 * encryption mode decides how they are stored: `portable` strips the Nextcloud
 * server side encryption layer so the artifact can be restored on any instance,
 * `raw` copies every column verbatim (restorable on this instance only). The end
 * to end encryption layer is never touched.
 */
readonly class BackupService {

	public function __construct(
		private ScopeReader    $scopeReader,
		private EncryptService $encryptService,
		private IAppManager    $appManager,
		private IConfig        $config,
	) {
	}

	/**
	 * Reads all rows of the requested scope into a backup archive.
	 *
	 * @param string $scope one of BackupManifest::SCOPE_*
	 * @param string $encryptionMode one of BackupManifest::MODE_*
	 * @param string|null $userId required for the user scope
	 * @param string|null $vaultGuid required for the vault scope
	 * @return BackupArchive
	 * @throws \InvalidArgumentException on an unknown scope/mode or a missing scope target
	 * @throws DoesNotExistException when the requested vault does not exist
	 * @throws \RuntimeException when data cannot be decrypted for a portable backup
	 */
	public function createBackup(string $scope, string $encryptionMode, ?string $userId = null, ?string $vaultGuid = null): BackupArchive {
		if (!BackupManifest::isValidScope($scope)) {
			throw new \InvalidArgumentException('Unknown backup scope: "' . $scope . '"');
		}
		if (!BackupManifest::isValidMode($encryptionMode)) {
			throw new \InvalidArgumentException('Unknown encryption mode: "' . $encryptionMode . '"');
		}
		if ($scope === BackupManifest::SCOPE_USER && ($userId === null || $userId === '')) {
			throw new \InvalidArgumentException('A user id is required for the "' . BackupManifest::SCOPE_USER . '" scope');
		}
		if ($scope === BackupManifest::SCOPE_VAULT && ($vaultGuid === null || $vaultGuid === '')) {
			throw new \InvalidArgumentException('A vault guid is required for the "' . BackupManifest::SCOPE_VAULT . '" scope');
		}

		$archive = new BackupArchive(new BackupManifest(
			BackupManifest::FORMAT_VERSION,
			$this->appManager->getAppVersion(Application::APP_ID),
			Utils::getTime(),
			$scope,
			$encryptionMode,
			$this->config->getSystemValueString('instanceid', ''),
			$scope === BackupManifest::SCOPE_USER ? $userId : null,
			$scope === BackupManifest::SCOPE_VAULT ? $vaultGuid : null,
		));

		$selection = $this->scopeReader->read($scope, $userId, $vaultGuid);
		$this->addVerbatimRows($archive, BackupArchive::SECTION_VAULTS, $selection->vaults);
		$this->addCredentials($archive, $selection->credentials);
		$this->addFiles($archive, $selection->files);
		$this->addRevisions($archive, $selection->revisions);
		$this->addVerbatimRows($archive, BackupArchive::SECTION_SHARING_ACL, $selection->sharingAcl);
		$this->addVerbatimRows($archive, BackupArchive::SECTION_SHARE_REQUESTS, $selection->shareRequests);
		$this->addVerbatimRows($archive, BackupArchive::SECTION_DELETE_VAULT_REQUESTS, $selection->deleteVaultRequests);

		$archive->refreshCounts();
		return $archive;
	}

	/**
	 * Limitations of the given options which the caller should present to the admin.
	 *
	 * @param string $scope one of BackupManifest::SCOPE_*
	 * @param string $encryptionMode one of BackupManifest::MODE_*
	 * @return string[]
	 */
	public function getCaveats(string $scope, string $encryptionMode): array {
		$caveats = [];

		if ($scope === BackupManifest::SCOPE_USER) {
			$caveats[] = 'Only file attachments owned by the exported user are included. Credential to file links live in the '
				. 'end to end encrypted "files" column, so attachments of credentials shared with this user cannot be resolved on the server.';
		}
		if ($scope === BackupManifest::SCOPE_VAULT) {
			$caveats[] = 'No file attachments are included. Credential to file links live in the end to end encrypted "files" '
				. 'column and cannot be resolved on the server, and file rows are not bound to a vault.';
		}
		if ($scope !== BackupManifest::SCOPE_INSTANCE) {
			$caveats[] = 'Sharing rows pointing to vaults or credentials outside of this scope are exported as well, but will be '
				. 'skipped on restore as long as those vaults or credentials are missing.';
		}
		if ($encryptionMode === BackupManifest::MODE_RAW) {
			$caveats[] = 'Raw backups keep the Nextcloud server side encryption layer and can only be restored on this instance '
				. '(instance id "' . $this->config->getSystemValueString('instanceid', '') . '") with the current config.php.';
		}

		return $caveats;
	}

	/**
	 * Adds rows of a section which holds no server side encrypted columns.
	 *
	 * @param array $entities entities exposing toBackupArray()
	 */
	private function addVerbatimRows(BackupArchive $archive, string $section, array $entities): void {
		$snapshot = $archive->section($section);
		foreach ($entities as $entity) {
			$snapshot->addRow($entity->toBackupArray());
		}
	}

	/**
	 * @param Credential[] $credentials
	 * @throws \RuntimeException when a portable backup cannot decrypt a credential
	 */
	private function addCredentials(BackupArchive $archive, array $credentials): void {
		$snapshot = $archive->section(BackupArchive::SECTION_CREDENTIALS);
		foreach ($credentials as $credential) {
			if (!$archive->manifest->isPortable()) {
				$snapshot->addRow($credential->toBackupArray());
				continue;
			}

			$stored = $this->pickEncryptedCredentialFields($credential->toBackupArray());
			$credential = $this->encryptService->decryptCredential($credential);
			$row = $credential->toBackupArray();
			$this->assertDecrypted($stored, $this->pickEncryptedCredentialFields($row), 'credential "' . $credential->getGuid() . '"');
			$snapshot->addRow($row);
		}
	}

	/**
	 * @param File[] $files
	 * @throws \RuntimeException when a portable backup cannot decrypt a file
	 */
	private function addFiles(BackupArchive $archive, array $files): void {
		$snapshot = $archive->section(BackupArchive::SECTION_FILES);
		foreach ($files as $file) {
			if (!$archive->manifest->isPortable()) {
				$snapshot->addRow($file->toBackupArray());
				continue;
			}

			$stored = ['filename' => $file->getFilename(), 'file_data' => $file->getFileData()];
			$file = $this->encryptService->decryptFile($file);
			$row = $file->toBackupArray();
			$this->assertDecrypted(
				$stored,
				['filename' => $row['filename'], 'file_data' => $row['file_data']],
				'file "' . $file->getGuid() . '"'
			);
			$snapshot->addRow($row);
		}
	}

	/**
	 * A revision stores a whole credential as base64(json(...)). A portable backup
	 * replaces that blob with the decrypted credential array, restore encodes it again.
	 *
	 * @param CredentialRevision[] $revisions
	 * @throws \RuntimeException when a portable backup cannot decode/decrypt a revision
	 */
	private function addRevisions(BackupArchive $archive, array $revisions): void {
		$snapshot = $archive->section(BackupArchive::SECTION_REVISIONS);
		foreach ($revisions as $revision) {
			$row = $revision->toBackupArray();
			if ($archive->manifest->isPortable()) {
				$row['credential_data'] = $this->decryptRevisionData($revision);
			}
			$snapshot->addRow($row);
		}
	}

	/**
	 * @return array<string, mixed> the decrypted credential of the revision
	 * @throws \RuntimeException
	 */
	private function decryptRevisionData(CredentialRevision $revision): array {
		$subject = 'revision "' . $revision->getGuid() . '"';
		$credential = json_decode(base64_decode((string)$revision->getCredentialData()), true);
		if (!is_array($credential)) {
			throw new \RuntimeException(sprintf(
				'The credential data of %s is not a base64 encoded json object. Use --encryption=%s to export it verbatim.',
				$subject,
				BackupManifest::MODE_RAW
			));
		}

		$stored = $this->pickEncryptedCredentialFields($credential);
		$credential = $this->encryptService->decryptCredential($credential);
		$this->assertDecrypted($stored, $this->pickEncryptedCredentialFields($credential), $subject);
		return $credential;
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array<string, mixed> only the server side encrypted credential columns
	 */
	private function pickEncryptedCredentialFields(array $row): array {
		return array_intersect_key($row, array_flip($this->encryptService->encrypted_credential_fields));
	}

	/**
	 * EncryptService returns false instead of throwing when the server key does not
	 * match the stored blob. Exporting that would silently destroy the affected
	 * values on restore, so a portable backup refuses to continue.
	 *
	 * @param array<string, mixed> $stored values as read from the database
	 * @param array<string, mixed> $decrypted the same fields after decryption
	 * @throws \RuntimeException
	 */
	private function assertDecrypted(array $stored, array $decrypted, string $subject): void {
		$failed = [];
		foreach ($stored as $field => $value) {
			if (($decrypted[$field] ?? null) === false && is_string($value) && $value !== '') {
				$failed[] = $field;
			}
		}

		if ($failed !== []) {
			throw new \RuntimeException(sprintf(
				'Could not decrypt %s of %s. The server side encryption key of this instance does not match the stored data. '
				. 'Use --encryption=%s to export it verbatim.',
				implode(', ', $failed),
				$subject,
				BackupManifest::MODE_RAW
			));
		}
	}
}
