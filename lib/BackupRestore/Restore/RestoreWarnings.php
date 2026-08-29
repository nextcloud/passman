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

/**
 * The admin-facing wording of the rows, a restore skips, because they reference a
 * vault or credential which is neither part of the artifact nor present on this instance.
 */
final class RestoreWarnings {

	public static function vaultOfCredentialMissing(?string $credentialGuid, ?int $vaultId): string {
		return sprintf(
			'Skipped the credential "%s": its vault (id %s) is not part of this backup',
			$credentialGuid ?? '?',
			$vaultId ?? 'null'
		);
	}

	public static function credentialOfRevisionMissing(?string $revisionGuid, ?int $credentialId): string {
		return sprintf(
			'Skipped the revision "%s": its credential (id %s) is not part of this backup',
			$revisionGuid ?? '?',
			$credentialId ?? 'null'
		);
	}

	public static function missingCredential(string $subject, ?string $itemGuid): string {
		return sprintf(
			'Skipped the %s of the credential "%s": the credential is neither part of this backup nor present on this instance',
			$subject,
			$itemGuid ?? '?'
		);
	}

	public static function missingVault(string $subject, ?string $itemGuid, ?string $vaultGuid): string {
		return sprintf(
			'Skipped the %s of the credential "%s": its vault "%s" is neither part of this backup nor present on this instance',
			$subject,
			$itemGuid ?? '?',
			$vaultGuid ?? '?'
		);
	}
}
