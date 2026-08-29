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
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\RestoreService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestoreLookup::class)]
class RestoreLookupTest extends TestCase {
	private function lookup(
		?VaultMapper $vaultMapper = null,
		?CredentialMapper $credentialMapper = null,
		?CredentialRevisionMapper $revisionMapper = null,
	): RestoreLookup {
		return new RestoreLookup(
			$vaultMapper ?? $this->createStub(VaultMapper::class),
			$credentialMapper ?? $this->createStub(CredentialMapper::class),
			$this->createStub(FileMapper::class),
			$revisionMapper ?? $this->createStub(CredentialRevisionMapper::class),
		);
	}

	public function testFindVaultReturnsNullWhenTheGuidDoesNotExist(): void {
		$vaultMapper = $this->createMock(VaultMapper::class);
		$vaultMapper->expects($this->once())
			->method('getByGuid')
			->with('missing')
			->willThrowException(new DoesNotExistException('missing'));

		$this->assertNull($this->lookup($vaultMapper)->findVault('missing'));
	}

	public function testFindVaultReturnsNullWithoutQueryingForANullGuid(): void {
		$vaultMapper = $this->createMock(VaultMapper::class);
		$vaultMapper->expects($this->never())->method('getByGuid');

		$this->assertNull($this->lookup($vaultMapper)->findVault(null));
	}

	public function testFindExistingRewritesAnAmbiguousGuidAsRuntimeException(): void {
		$cause = new MultipleObjectsReturnedException('dup');

		try {
			$this->lookup()->findExisting(static fn() => throw $cause, 'vault "dup-guid"');
			$this->fail('RestoreLookup must not leak MultipleObjectsReturnedException to the caller');
		} catch (\RuntimeException $e) {
			$this->assertSame($cause, $e->getPrevious());
			$this->assertStringContainsString('more than one vault "dup-guid"', $e->getMessage());
			$this->assertStringContainsString('--mode=' . RestoreService::MODE_REPLACE, $e->getMessage());
		}
	}

	public function testFindVaultPipesADuplicateMapperResultThroughFindExisting(): void {
		$cause = new MultipleObjectsReturnedException('dup');
		$vaultMapper = $this->createMock(VaultMapper::class);
		$vaultMapper->expects($this->once())
			->method('getByGuid')
			->with('dup-guid')
			->willThrowException($cause);

		try {
			$this->lookup($vaultMapper)->findVault('dup-guid');
			$this->fail('RestoreLookup must not leak MultipleObjectsReturnedException to the caller');
		} catch (\RuntimeException $e) {
			$this->assertSame($cause, $e->getPrevious());
		}
	}

	public function testResolveCredentialIdUsesTheIdMapBeforeGuidFallback(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$context->rememberCredential(10, 99);
		$credentialMapper = $this->createMock(CredentialMapper::class);
		$credentialMapper->expects($this->never())->method('getCredentialByGUID');

		$this->assertSame(99, $this->lookup(credentialMapper: $credentialMapper)->resolveCredentialId($context, 10, 'cred-guid'));
	}

	public function testResolveCredentialIdFallsBackToGuidWhenTheOldIdIsUnmapped(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$credential = Credential::fromRow(['id' => 50, 'guid' => 'cred-guid']);
		$credentialMapper = $this->createMock(CredentialMapper::class);
		$credentialMapper->expects($this->once())
			->method('getCredentialByGUID')
			->with('cred-guid')
			->willReturn($credential);

		$this->assertSame(50, $this->lookup(credentialMapper: $credentialMapper)->resolveCredentialId($context, 10, 'cred-guid'));
	}

	public function testResolveVaultIdFallsBackToGuidWhenTheOldIdIsUnmapped(): void {
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$vault = Vault::fromRow(['id' => 7, 'guid' => 'vault-guid']);
		$vaultMapper = $this->createMock(VaultMapper::class);
		$vaultMapper->expects($this->once())
			->method('getByGuid')
			->with('vault-guid')
			->willReturn($vault);

		$this->assertSame(7, $this->lookup($vaultMapper)->resolveVaultId($context, 1, 'vault-guid'));
	}

	public function testRevisionIdsByGuidIndexesByGuid(): void {
		$first = CredentialRevision::fromRow(['id' => 3, 'guid' => 'rev-a']);
		$second = CredentialRevision::fromRow(['id' => 4, 'guid' => 'rev-b']);
		$revisionMapper = $this->createMock(CredentialRevisionMapper::class);
		$revisionMapper->expects($this->once())->method('getRevisions')->with(20)->willReturn([$first, $second]);

		$this->assertSame(['rev-a' => 3, 'rev-b' => 4], $this->lookup(revisionMapper: $revisionMapper)->revisionIdsByGuid(20));
	}
}
