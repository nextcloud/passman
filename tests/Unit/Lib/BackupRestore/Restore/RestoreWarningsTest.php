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

namespace OCA\Passman\Tests\Unit\Lib\BackupRestore\Restore;

use OCA\Passman\BackupRestore\Restore\RestoreWarnings;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestoreWarnings::class)]
class RestoreWarningsTest extends TestCase {
	public function testVaultOfCredentialMissingUsesPlaceholdersForNulls(): void {
		$this->assertSame(
			'Skipped the credential "?": its vault (id null) is not part of this backup',
			RestoreWarnings::vaultOfCredentialMissing(null, null)
		);
		$this->assertSame(
			'Skipped the credential "cred-1": its vault (id 4) is not part of this backup',
			RestoreWarnings::vaultOfCredentialMissing('cred-1', 4)
		);
	}

	public function testCredentialOfRevisionMissing(): void {
		$this->assertSame(
			'Skipped the revision "rev-1": its credential (id 9) is not part of this backup',
			RestoreWarnings::credentialOfRevisionMissing('rev-1', 9)
		);
	}

	public function testMissingCredentialAndVault(): void {
		$this->assertSame(
			'Skipped the sharing acl entry of the credential "cred-1": the credential is neither part of this backup nor present on this instance',
			RestoreWarnings::missingCredential('sharing acl entry', 'cred-1')
		);
		$this->assertSame(
			'Skipped the share request of the credential "cred-1": its vault "vault-1" is neither part of this backup nor present on this instance',
			RestoreWarnings::missingVault('share request', 'cred-1', 'vault-1')
		);
	}
}
