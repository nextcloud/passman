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

use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Service\RestoreService;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestoreContext::class)]
class RestoreContextTest extends TestCase {
	public function testVaultIdResolvesRememberedOldId(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());

		$context->rememberVault(10, 100);

		$this->assertSame(100, $context->vaultId(10));

		// ensure vaultId() accepts numeric string like from decoded artifact rows
		$this->assertSame(100, $context->vaultId('10'));
	}

	public function testVaultIdReturnsNullForUnmappedOrNullOldId(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());

		$context->rememberVault(10, 100);

		$this->assertNull($context->vaultId(999));
		$this->assertNull($context->vaultId(null));
	}

	public function testRememberVaultIgnoresNullOldId(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());

		$context->rememberVault(null, 5);

		$this->assertNull($context->vaultId(5));
	}

	public function testCredentialIdIsMappedIndependentlyFromVaultId(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());

		$context->rememberVault(10, 100);
		$context->rememberCredential(10, 200);

		// well, basically stupid, but would be a mess having that mixed - so better test it :D
		$this->assertSame(100, $context->vaultId(10));
		$this->assertSame(200, $context->credentialId(10));
	}

	public function testIsMergeReflectsConstructorMode(): void {
		$merge = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$replace = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->assertTrue($merge->isMerge());
		$this->assertFalse($replace->isMerge());
	}
}
