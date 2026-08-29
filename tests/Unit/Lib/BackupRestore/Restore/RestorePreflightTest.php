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

use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\Restore\RestorePreflight;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Service\RestoreService;
use OCP\IConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Test\TestCase;

#[CoversClass(RestorePreflight::class)]
class RestorePreflightTest extends TestCase {
	private IConfig&Stub     $config;
	private RestorePreflight $preflight;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createStub(IConfig::class);
		$this->config->method('getSystemValueString')->willReturn('this-instance');

		$this->preflight = new RestorePreflight($this->config);
	}

	public function testForeignInstanceRawBackupIsRejected(): void {
		$manifest = $this->manifest(encryptionMode: BackupManifest::MODE_RAW, instanceId: 'other-instance');

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/other-instance.*this-instance/');

		$this->preflight->assertRestorable($manifest, false);
	}

	public function testForceAllowsRestoringAForeignInstanceRawBackup(): void {
		$manifest = $this->manifest(encryptionMode: BackupManifest::MODE_RAW, instanceId: 'other-instance');

		$this->preflight->assertRestorable($manifest, true);

		// count successful assertRestorable manually; otherwise an exception would have been thrown
		$this->addToAssertionCount(1);
	}

	public function testMatchingInstanceRawBackupIsNotRejectedWithoutForce(): void {
		$manifest = $this->manifest(encryptionMode: BackupManifest::MODE_RAW, instanceId: 'this-instance');

		$this->preflight->assertRestorable($manifest, false);

		// count successful assertRestorable manually; otherwise an exception would have been thrown
		$this->addToAssertionCount(1);
	}

	public function testMissingUserScopeTargetIsRejected(): void {
		$manifest = $this->manifest(scope: BackupManifest::SCOPE_USER, targetUserId: null);

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/"user" scope but no target user id/');

		$this->preflight->assertRestorable($manifest, false);
	}

	public function testMissingVaultScopeTargetIsRejected(): void {
		$manifest = $this->manifest(scope: BackupManifest::SCOPE_VAULT, targetVaultGuid: null);

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/"vault" scope but no target vault guid/');

		$this->preflight->assertRestorable($manifest, false);
	}

	public function testMergeCaveatIsIncludedOnlyForMergeMode(): void {
		$manifest = $this->manifest();

		$merge = $this->preflight->caveats($manifest, RestoreService::MODE_MERGE);
		$replace = $this->preflight->caveats($manifest, RestoreService::MODE_REPLACE);

		$this->assertStringContainsString('Merge only touches rows of the artifact', $merge[0]);
		$this->assertStringNotContainsString('Merge only touches rows of the artifact', implode("\n", $replace));
	}

	public function testPortableCaveatIsIncludedOnlyForPortableArtifacts(): void {
		$portable = $this->preflight->caveats($this->manifest(), RestoreService::MODE_REPLACE);
		$raw = $this->preflight->caveats(
			$this->manifest(encryptionMode: BackupManifest::MODE_RAW),
			RestoreService::MODE_REPLACE
		);

		$this->assertStringContainsString('server side encryption layer is re-applied', implode("\n", $portable));
		$this->assertStringNotContainsString('server side encryption layer is re-applied', implode("\n", $raw));
	}

	public function testNonInstanceCaveatWarnsAboutSkippedSharingRows(): void {
		$user = $this->preflight->caveats(
			$this->manifest(scope: BackupManifest::SCOPE_USER, targetUserId: 'alice'),
			RestoreService::MODE_REPLACE
		);
		$instance = $this->preflight->caveats($this->manifest(), RestoreService::MODE_REPLACE);

		$this->assertStringContainsString('Sharing rows of the artifact', implode("\n", $user));
		$this->assertStringNotContainsString('Sharing rows of the artifact', implode("\n", $instance));
	}

	private function manifest(
		string  $scope = BackupManifest::SCOPE_INSTANCE,
		string  $encryptionMode = BackupManifest::MODE_PORTABLE,
		string  $instanceId = 'this-instance',
		?string $targetUserId = null,
		?string $targetVaultGuid = null,
	): BackupManifest {
		return new BackupManifest(
			BackupManifest::FORMAT_VERSION,
			'1.0.0',
			0,
			$scope,
			$encryptionMode,
			$instanceId,
			$targetUserId,
			$targetVaultGuid,
		);
	}
}
