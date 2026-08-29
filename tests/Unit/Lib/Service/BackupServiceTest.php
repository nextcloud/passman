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

namespace OCA\Passman\Tests\Unit\Lib\Service;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\ScopeReader;
use OCA\Passman\BackupRestore\ScopeSelection;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\Vault;
use OCA\Passman\Service\BackupService;
use OCA\Passman\Service\EncryptService;
use OCP\App\IAppManager;
use OCP\IConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Test\TestCase;

#[CoversClass(BackupService::class)]
class BackupServiceTest extends TestCase {
	private ScopeReader&Stub    $scopeReader;
	private EncryptService&Stub $encryptService;
	private IAppManager&Stub    $appManager;
	private IConfig&Stub        $config;
	private BackupService       $service;

	private const APP_VERSION = '2.6.1';
	private const INSTANCE_ID = 'this-instance';

	protected function setUp(): void {
		parent::setUp();

		$this->scopeReader = $this->createStub(ScopeReader::class);
		$this->encryptService = $this->createStub(EncryptService::class);
		$this->appManager = $this->createStub(IAppManager::class);
		$this->config = $this->createStub(IConfig::class);

		$this->appManager->method('getAppVersion')->willReturn(self::APP_VERSION);
		$this->config->method('getSystemValueString')->willReturn(self::INSTANCE_ID);

		$this->service = new BackupService(
			$this->scopeReader,
			$this->encryptService,
			$this->appManager,
			$this->config,
		);
	}

	public function testInvalidScopeThrows(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/Unknown backup scope/');

		$this->service->createBackup('nope', BackupManifest::MODE_PORTABLE);
	}

	public function testInvalidEncryptionModeThrows(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/Unknown encryption mode/');

		$this->service->createBackup(BackupManifest::SCOPE_INSTANCE, 'nope');
	}

	public function testUserScopeRequiresAUserId(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/user id is required/');

		$this->service->createBackup(BackupManifest::SCOPE_USER, BackupManifest::MODE_PORTABLE);
	}

	public function testVaultScopeRequiresAVaultGuid(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/vault guid is required/');

		$this->service->createBackup(BackupManifest::SCOPE_VAULT, BackupManifest::MODE_PORTABLE, vaultGuid: '');
	}

	public function testUserCaveatMentionsOwnerFilesOnly(): void {
		$caveats = $this->service->getCaveats(BackupManifest::SCOPE_USER, BackupManifest::MODE_PORTABLE);

		$this->assertStringContainsString('Only file attachments owned by the exported user', $caveats[0]);
		$this->assertStringContainsString('skipped on restore', implode("\n", $caveats));
	}

	public function testVaultCaveatMentionsMissingFiles(): void {
		$caveats = $this->service->getCaveats(BackupManifest::SCOPE_VAULT, BackupManifest::MODE_PORTABLE);

		$this->assertStringContainsString('No file attachments are included', $caveats[0]);
	}

	public function testRawCaveatMentionsThisInstanceId(): void {
		$caveats = $this->service->getCaveats(BackupManifest::SCOPE_INSTANCE, BackupManifest::MODE_RAW);

		$this->assertCount(1, $caveats);
		$this->assertStringContainsString('this-instance', $caveats[0]);
		$this->assertStringContainsString('Raw backups keep the Nextcloud server side encryption layer', $caveats[0]);
	}

	private function serviceDecrypting(Credential $stored, Credential $afterDecrypt): BackupService {
		$scopeReader = $this->createMock(ScopeReader::class);
		$scopeReader->expects($this->once())
			->method('read')
			->willReturn(new ScopeSelection(credentials: [$stored]));

		$encryptService = $this->createMock(EncryptService::class);
		$encryptService->expects($this->once())->method('decryptCredential')->willReturn($afterDecrypt);

		return new BackupService($scopeReader, $encryptService, $this->appManager, $this->config);
	}

	/**
	 * @param array<string, mixed> $overrides
	 */
	private function credential(array $overrides): Credential {
		return Credential::fromRow(array_merge([
			'id' => 1,
			'guid' => 'cred-1',
			'user_id' => 'alice',
			'vault_id' => 1,
			'label' => 'Note',
			'username' => 'alice',
		], $overrides));
	}

	public function testPortableBackupAbortsWhenDecryptLeavesFalseForANonEmptyField(): void {
		$stored = $this->credential(['guid' => 'cred-1', 'description' => 'ciphertext']);
		$afterDecrypt = $this->credential(['guid' => 'cred-1', 'description' => false]);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches(
			'/Could not decrypt description of credential "cred-1".*--encryption=' . BackupManifest::MODE_RAW . '/'
		);

		$this->serviceDecrypting($stored, $afterDecrypt)
			->createBackup(BackupManifest::SCOPE_INSTANCE, BackupManifest::MODE_PORTABLE);
	}

	public function testRawBackupCopiesVaultRowsVerbatim(): void {
		$vault = Vault::fromRow([
			'id' => 1,
			'guid' => 'vault-1',
			'name' => 'Personal',
			'user_id' => 'alice',
		]);
		$scopeReader = $this->createMock(ScopeReader::class);
		$scopeReader->expects($this->once())
			->method('read')
			->willReturn(new ScopeSelection(vaults: [$vault]));
		$encryptService = $this->createMock(EncryptService::class);
		$encryptService->expects($this->never())->method('decryptCredential');

		$service = new BackupService($scopeReader, $encryptService, $this->appManager, $this->config);
		$archive = $service->createBackup(BackupManifest::SCOPE_INSTANCE, BackupManifest::MODE_RAW);

		$this->assertSame([$vault->toBackupArray()], $archive->section(BackupArchive::SECTION_VAULTS)->rows);
		$this->assertSame(self::APP_VERSION, $archive->manifest->appVersion);
		$this->assertSame(self::INSTANCE_ID, $archive->manifest->instanceId);
	}
}
