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

use OCA\Passman\AppInfo\Application;
use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\ScopeReader;
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Service\BackupService;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\DbTestTrait;
use OCA\Passman\Utility\Utils;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

/**
 * DB round-trips of the current backup/restore behavior.
 *
 * Replace tests use the user/vault scopes so they do not wipe other users on the
 * shared test instance. The restorers, encryption, GUID preservation and integer
 * id remapping are the same paths an instance restore uses. ScopeReader's
 * instance/user/vault selection is asserted via backup section counts.
 */
#[Group(name: 'DB')]
#[CoversClass(BackupService::class)]
#[CoversClass(RestoreService::class)]
#[CoversClass(ScopeReader::class)]
class BackupRestoreRoundTripTest extends TestCase {
	use DbTestTrait;

	private const USER_A = self::PASSMAN_BACKUP_RESTORE_USER_PREFIX . 'rt_a';
	private const USER_B = self::PASSMAN_BACKUP_RESTORE_USER_PREFIX . 'rt_b';

	/** @var list<string> columns whose values are regenerated or remapped on restore */
	private const ID_AND_FK_COLUMNS = [
		BackupArchive::SECTION_VAULTS                => ['id'],
		BackupArchive::SECTION_CREDENTIALS           => ['id', 'vault_id'],
		BackupArchive::SECTION_FILES                 => ['id'],
		BackupArchive::SECTION_REVISIONS             => ['id', 'credential_id'],
		BackupArchive::SECTION_SHARING_ACL           => ['id', 'item_id', 'vault_id'],
		BackupArchive::SECTION_SHARE_REQUESTS        => ['id', 'item_id', 'target_vault_id'],
		BackupArchive::SECTION_DELETE_VAULT_REQUESTS => ['id'],
	];

	private IDBConnection            $db;
	private VaultMapper              $vaultMapper;
	private CredentialMapper         $credentialMapper;
	private FileMapper               $fileMapper;
	private CredentialRevisionMapper $revisionMapper;
	private SharingACLMapper         $sharingACLMapper;
	private EncryptService           $encryptService;
	private BackupService            $backupService;
	private RestoreService           $restoreService;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$utils = new Utils();
		$this->vaultMapper = new VaultMapper($this->db, $utils);
		$this->credentialMapper = new CredentialMapper($this->db, $utils);
		$this->fileMapper = new FileMapper($this->db, $utils);
		$this->revisionMapper = new CredentialRevisionMapper($this->db, $utils);
		$this->sharingACLMapper = new SharingACLMapper($this->db);

		$container = (new Application())->getContainer();
		$this->encryptService = $container->get(EncryptService::class);
		$this->backupService = $container->get(BackupService::class);
		$this->restoreService = $container->get(RestoreService::class);

		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deleteAllPassmanRowsForUsers($this->db, self::USER_A, self::USER_B);
	}

	public function testPortableReplaceRoundTripPreservesGuidsAndDecryptedFields(): void {
		$seed = $this->seedGraph(self::USER_A);
		$before = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);

		$result = $this->restoreService->restore($before, RestoreService::MODE_REPLACE);

		$this->assertSame(0, $result->totalSkipped());
		$this->assertSame(0, $result->totalUpdated());
		$this->assertSame($before->manifest->counts[BackupArchive::SECTION_CREDENTIALS], $result->inserted[BackupArchive::SECTION_CREDENTIALS]);

		$after = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);
		$this->assertSectionContentsMatch($before, $after);
		$this->assertForeignKeysAreRemappedConsistently($before, $after);

		$restored = $this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid());
		$this->assertNotSame('secret', $restored->getPassword(), 'portable restore must re-apply the server side encryption layer');
		$this->assertSame('secret', $this->encryptService->decryptCredential($restored)->getPassword());

		$empty = $this->credentialMapper->getCredentialByGUID($seed['emptyCredential']->getGuid());
		$this->assertNull($empty->getDescription());
		$this->assertNull($empty->getOtp());
	}

	public function testRawReplaceRoundTripPreservesOriginalCiphertext(): void {
		$seed = $this->seedGraph(self::USER_A);
		$storedPassword = $this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid())->getPassword();
		$before = $this->backupUser(self::USER_A, BackupManifest::MODE_RAW);

		$this->restoreService->restore($before, RestoreService::MODE_REPLACE);

		$after = $this->backupUser(self::USER_A, BackupManifest::MODE_RAW);
		$this->assertSectionContentsMatch($before, $after);
		$this->assertForeignKeysAreRemappedConsistently($before, $after);

		$restored = $this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid());
		$this->assertSame($storedPassword, $restored->getPassword());
		$this->assertSame(
			$before->section(BackupArchive::SECTION_FILES)->rows[0]['file_data'],
			$after->section(BackupArchive::SECTION_FILES)->rows[0]['file_data'],
		);
	}

	public function testMergeUpdatesExistingInsertsMissingRelinksAclAndKeepsExtraRows(): void {
		$seed = $this->seedGraph(self::USER_A, withSecondVault: false);
		$archive = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);
		$originalLabel = $seed['credential']->getLabel();

		$seed['credential']->setLabel('tampered-label');
		$this->credentialMapper->upd($seed['credential']);
		$this->credentialMapper->deleteCredential($seed['credential']);
		$extra = $this->createEncryptedCredential($seed['vault']->getId(), self::USER_A, ['label' => 'not-in-artifact']);

		$result = $this->restoreService->restore($archive, RestoreService::MODE_MERGE);

		$this->assertSame(0, $result->totalDeleted());
		$this->assertGreaterThan(0, $result->inserted[BackupArchive::SECTION_CREDENTIALS]);
		$this->assertSame(
			$originalLabel,
			$this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid())->getLabel(),
			'merge must restore the artifact label, not the tampered one',
		);
		$this->assertSame(
			$extra->getGuid(),
			$this->credentialMapper->getCredentialByGUID($extra->getGuid())->getGuid(),
			'merge must keep rows that exist on this instance but not in the artifact',
		);

		$reinserted = $this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid());
		$acl = $this->sharingACLMapper->getItemACL(self::USER_B, $seed['credential']->getGuid());
		$this->assertSame($reinserted->getId(), $acl->getItemId());
		$this->assertSame($seed['vault']->getId(), $acl->getVaultId());
	}

	public function testUserReplaceLeavesOtherUsersUntouched(): void {
		$other = $this->seedMinimal(self::USER_B);
		$this->seedGraph(self::USER_A, withSecondVault: false);
		$archive = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);

		$this->restoreService->restore($archive, RestoreService::MODE_REPLACE);

		$stillThere = $this->credentialMapper->getCredentialByGUID($other['credential']->getGuid());
		$this->assertSame($other['credential']->getId(), $stillThere->getId());
		$this->assertSame($other['vault']->getId(), $this->vaultMapper->getByGuid($other['vault']->getGuid())->getId());
		$this->assertCount(1, $this->credentialMapper->getByUser(self::USER_B));
	}

	public function testVaultScopeBackupOmitsFilesAndReplaceLeavesOtherVaults(): void {
		$seed = $this->seedGraph(self::USER_A);
		$archive = $this->backupService->createBackup(
			BackupManifest::SCOPE_VAULT,
			BackupManifest::MODE_PORTABLE,
			vaultGuid: $seed['vault']->getGuid(),
		);

		$this->assertSame(0, $archive->section(BackupArchive::SECTION_FILES)->count());
		$this->assertSame(1, $archive->section(BackupArchive::SECTION_VAULTS)->count());
		$this->assertSame(1, $archive->section(BackupArchive::SECTION_CREDENTIALS)->count());
		$this->assertSame($seed['credential']->getGuid(), $archive->section(BackupArchive::SECTION_CREDENTIALS)->rows[0]['guid']);

		$this->restoreService->restore($archive, RestoreService::MODE_REPLACE);

		$this->assertSame($seed['otherVault']->getId(), $this->vaultMapper->getByGuid($seed['otherVault']->getGuid())->getId());
		$this->assertSame(
			$seed['file']->getId(),
			$this->fileMapper->getFileByGuid($seed['file']->getGuid())->getId(),
			'vault scope does not own file rows, so replace must not delete them',
		);
	}

	public function testRestoreFailureAfterTheFirstInsertRollsTheTransactionBack(): void {
		$seed = $this->seedGraph(self::USER_A, withSecondVault: false);
		$beforeIds = $this->idsForUser(self::USER_A);
		$archive = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);
		$archive->section(BackupArchive::SECTION_CREDENTIALS)->rows[0]['not_a_column'] = 'nope';

		try {
			$this->restoreService->restore($archive, RestoreService::MODE_REPLACE);
			$this->fail('restore must abort on an unknown column');
		} catch (InvalidBackupException $e) {
			$this->assertStringContainsString('unknown column "not_a_column"', $e->getMessage());
		}

		$this->assertSame($beforeIds, $this->idsForUser(self::USER_A));
		$this->assertSame(
			$seed['credential']->getId(),
			$this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid())->getId(),
		);
	}

	public function testDryRunLeavesRowsUnchangedAndCountsMatchASubsequentRestore(): void {
		$seed = $this->seedGraph(self::USER_A, withSecondVault: false);
		$beforeIds = $this->idsForUser(self::USER_A);
		$archive = $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE);

		$dry = $this->restoreService->restore($archive, RestoreService::MODE_REPLACE, dryRun: true);

		$this->assertSame($beforeIds, $this->idsForUser(self::USER_A));
		$this->assertSame(
			$seed['credential']->getId(),
			$this->credentialMapper->getCredentialByGUID($seed['credential']->getGuid())->getId(),
		);
		$this->assertSectionContentsMatch($archive, $this->backupUser(self::USER_A, BackupManifest::MODE_PORTABLE));
		$this->assertGreaterThan(0, $dry->totalDeleted());
		$this->assertGreaterThan(0, $dry->totalInserted());
		$this->assertSame(0, $dry->totalUpdated());

		$real = $this->restoreService->restore($archive, RestoreService::MODE_REPLACE);

		$this->assertSame($dry->deleted, $real->deleted);
		$this->assertSame($dry->inserted, $real->inserted);
		$this->assertSame($dry->updated, $real->updated);
		$this->assertSame($dry->skipped, $real->skipped);
		$this->assertNotSame(
			$beforeIds['credentials'],
			$this->idsForUser(self::USER_A)['credentials'],
			'the subsequent real restore must still rewrite numeric ids',
		);
	}

	public function testForeignRawRestoreIsRejectedWithoutForceAndInsertsWithForce(): void {
		$this->seedGraph(self::USER_A, withSecondVault: false);
		$archive = $this->backupUser(self::USER_A, BackupManifest::MODE_RAW);
		$archive->manifest->instanceId = 'foreign-instance-id';
		$beforeIds = $this->idsForUser(self::USER_A);

		try {
			$this->restoreService->restore($archive, RestoreService::MODE_REPLACE, false);
			$this->fail('raw artifacts of a foreign instance must be rejected without --force');
		} catch (InvalidBackupException $e) {
			$this->assertStringContainsString('foreign-instance-id', $e->getMessage());
		}
		$this->assertSame($beforeIds, $this->idsForUser(self::USER_A));

		$result = $this->restoreService->restore($archive, RestoreService::MODE_REPLACE, true);
		$this->assertSame(0, $result->totalSkipped());
		$this->assertGreaterThan(0, $result->totalInserted());
		$this->assertNotSame(
			$beforeIds['credentials'],
			$this->idsForUser(self::USER_A)['credentials'],
			'force restore of a foreign raw artifact still regenerates numeric ids',
		);
	}

	public function testScopeReaderCountsMatchUserVaultAndInstanceSelections(): void {
		$seedA = $this->seedGraph(self::USER_A);
		$seedB = $this->seedMinimal(self::USER_B);

		$userA = $this->backupUser(self::USER_A, BackupManifest::MODE_RAW);
		$userB = $this->backupUser(self::USER_B, BackupManifest::MODE_RAW);
		$vault = $this->backupService->createBackup(
			BackupManifest::SCOPE_VAULT,
			BackupManifest::MODE_RAW,
			vaultGuid: $seedA['vault']->getGuid(),
		);
		$instance = $this->backupService->createBackup(BackupManifest::SCOPE_INSTANCE, BackupManifest::MODE_RAW);

		$this->assertSame(2, $userA->section(BackupArchive::SECTION_VAULTS)->count());
		$this->assertSame(2, $userA->section(BackupArchive::SECTION_CREDENTIALS)->count());
		$this->assertSame(1, $userA->section(BackupArchive::SECTION_FILES)->count());
		$this->assertSame(1, $userA->section(BackupArchive::SECTION_REVISIONS)->count());
		$this->assertSame(1, $userA->section(BackupArchive::SECTION_SHARING_ACL)->count());

		$this->assertSame(1, $userB->section(BackupArchive::SECTION_VAULTS)->count());
		$this->assertSame(1, $userB->section(BackupArchive::SECTION_CREDENTIALS)->count());
		$this->assertSame(0, $userB->section(BackupArchive::SECTION_FILES)->count());

		$this->assertSame(1, $vault->section(BackupArchive::SECTION_VAULTS)->count());
		$this->assertSame(1, $vault->section(BackupArchive::SECTION_CREDENTIALS)->count());
		$this->assertSame(0, $vault->section(BackupArchive::SECTION_FILES)->count());
		$this->assertSame(1, $vault->section(BackupArchive::SECTION_REVISIONS)->count());

		$this->assertGreaterThanOrEqual(
			$userA->section(BackupArchive::SECTION_VAULTS)->count() + $userB->section(BackupArchive::SECTION_VAULTS)->count(),
			$instance->section(BackupArchive::SECTION_VAULTS)->count(),
		);
		$this->assertGreaterThanOrEqual(
			$userA->section(BackupArchive::SECTION_CREDENTIALS)->count() + $userB->section(BackupArchive::SECTION_CREDENTIALS)->count(),
			$instance->section(BackupArchive::SECTION_CREDENTIALS)->count(),
		);
		$this->assertContains($seedA['vault']->getGuid(), $this->guids($instance->section(BackupArchive::SECTION_VAULTS)->rows));
		$this->assertContains($seedB['vault']->getGuid(), $this->guids($instance->section(BackupArchive::SECTION_VAULTS)->rows));
		$this->assertContains($seedA['file']->getGuid(), $this->guids($instance->section(BackupArchive::SECTION_FILES)->rows));
	}

	/**
	 * @return array{
	 *   vault: Vault,
	 *   otherVault: Vault|null,
	 *   credential: Credential,
	 *   emptyCredential: Credential|null,
	 *   file: File,
	 *   revision: CredentialRevision,
	 *   acl: SharingACL
	 * }
	 */
	private function seedGraph(string $userId, bool $withSecondVault = true): array {
		$vault = $this->vaultMapper->create('Round-trip vault', $userId);
		$otherVault = $withSecondVault ? $this->vaultMapper->create('Other vault', $userId) : null;

		$credential = $this->createEncryptedCredential($vault->getId(), $userId, ['label' => 'Login']);
		$emptyCredential = $withSecondVault
			? $this->createEncryptedCredential($otherVault->getId(), $userId, ['label' => 'Empty fields'])
			: null;
		if ($emptyCredential !== null) {
			$this->nullServerEncryptedFields($emptyCredential->getId(), ['description', 'otp']);
			$emptyCredential = $this->credentialMapper->getCredentialByGUID($emptyCredential->getGuid());
		}

		$file = $this->createEncryptedFile($userId);
		$revision = $this->revisionMapper->create(
			$credential->toBackupArray(),
			$userId,
			$credential->getId(),
			$userId,
		);
		$acl = $this->createAcl($credential, $vault, self::USER_B);

		return [
			'vault'           => $vault,
			'otherVault'      => $otherVault,
			'credential'      => $credential,
			'emptyCredential' => $emptyCredential,
			'file'            => $file,
			'revision'        => $revision,
			'acl'             => $acl,
		];
	}

	/**
	 * @return array{vault: Vault, credential: Credential}
	 */
	private function seedMinimal(string $userId): array {
		$vault = $this->vaultMapper->create('Minimal vault', $userId);
		$credential = $this->createEncryptedCredential($vault->getId(), $userId, ['label' => 'Minimal']);
		return ['vault' => $vault, 'credential' => $credential];
	}

	private function createEncryptedCredential(int $vaultId, string $userId, array $overrides = []): Credential {
		$data = $this->encryptService->encryptCredential($this->sampleCredentialData($vaultId, $userId, $overrides));
		return $this->credentialMapper->create($data);
	}

	/**
	 * Not e2e encrypted, but should be fine for only the backend tests.
	 * @param string $userId
	 * @return File
	 * @throws \Exception
	 */
	private function createEncryptedFile(string $userId): File {
		$fileData = 'attachment-bytes';
		$encrypted = $this->encryptService->encryptFile([
			'filename'  => 'note.txt',
			'file_data' => $fileData,
			'mimetype'  => 'text/plain',
			'size'      => strlen($fileData),
		]);
		return $this->fileMapper->create($encrypted, $userId);
	}

	private function createAcl(Credential $credential, Vault $vault, string $userId): SharingACL {
		$acl = new SharingACL();
		$acl->setItemId($credential->getId());
		$acl->setItemGuid($credential->getGuid());
		$acl->setUserId($userId);
		$acl->setCreated(Utils::getTime());
		$acl->setExpire(0);
		$acl->setExpireViews(0);
		$acl->setPermissions(7);
		$acl->setVaultId($vault->getId());
		$acl->setVaultGuid($vault->getGuid());
		$acl->setSharedKey('shared-key');
		return $this->sharingACLMapper->createACLEntry($acl);
	}

	/**
	 * @param string[] $columns
	 */
	private function nullServerEncryptedFields(int $credentialId, array $columns): void {
		$qb = $this->db->getQueryBuilder();
		$update = $qb->update('passman_credentials');
		foreach ($columns as $column) {
			$update->set($column, $qb->createNamedParameter(null, IQueryBuilder::PARAM_NULL));
		}
		$update->where($qb->expr()->eq('id', $qb->createNamedParameter($credentialId, IQueryBuilder::PARAM_INT)));
		$update->executeStatement();
	}

	private function backupUser(string $userId, string $encryptionMode): BackupArchive {
		return $this->backupService->createBackup(
			BackupManifest::SCOPE_USER,
			$encryptionMode,
			userId: $userId,
		);
	}

	private function assertSectionContentsMatch(BackupArchive $before, BackupArchive $after): void {
		foreach (BackupArchive::SECTIONS as $section) {
			$this->assertEquals(
				$this->comparableRows($section, $before->section($section)->rows),
				$this->comparableRows($section, $after->section($section)->rows),
				$section . ' content must match after restore, ignoring regenerated ids and remapped foreign keys',
			);
		}
	}

	private function assertForeignKeysAreRemappedConsistently(BackupArchive $before, BackupArchive $after): void {
		$vaultsBefore = $this->indexByGuid($before->section(BackupArchive::SECTION_VAULTS)->rows);
		$vaultsAfter = $this->indexByGuid($after->section(BackupArchive::SECTION_VAULTS)->rows);
		$credsBefore = $this->indexByGuid($before->section(BackupArchive::SECTION_CREDENTIALS)->rows);
		$credsAfter = $this->indexByGuid($after->section(BackupArchive::SECTION_CREDENTIALS)->rows);

		foreach ($credsBefore as $guid => $oldCredential) {
			$vaultGuid = $this->guidOfId($vaultsBefore, (int)$oldCredential['vault_id']);
			$this->assertSame(
				(int)$vaultsAfter[$vaultGuid]['id'],
				(int)$credsAfter[$guid]['vault_id'],
			);
		}

		$revisionsAfter = $this->indexByGuid($after->section(BackupArchive::SECTION_REVISIONS)->rows);
		foreach ($before->section(BackupArchive::SECTION_REVISIONS)->rows as $oldRevision) {
			$credentialGuid = $this->guidOfId($credsBefore, (int)$oldRevision['credential_id']);
			$this->assertSame(
				(int)$credsAfter[$credentialGuid]['id'],
				(int)$revisionsAfter[$oldRevision['guid']]['credential_id'],
			);
		}

		foreach ($before->section(BackupArchive::SECTION_SHARING_ACL)->rows as $oldAcl) {
			$newAcl = $this->findAcl($after->section(BackupArchive::SECTION_SHARING_ACL)->rows, $oldAcl);
			$credentialGuid = $this->guidOfId($credsBefore, (int)$oldAcl['item_id']);
			$vaultGuid = $this->guidOfId($vaultsBefore, (int)$oldAcl['vault_id']);
			$this->assertSame((int)$credsAfter[$credentialGuid]['id'], (int)$newAcl['item_id']);
			$this->assertSame((int)$vaultsAfter[$vaultGuid]['id'], (int)$newAcl['vault_id']);
		}
	}

	/**
	 * @param list<array<string, mixed>> $rows
	 * @return list<array<string, mixed>>
	 */
	private function comparableRows(string $section, array $rows): array {
		$ignore = self::ID_AND_FK_COLUMNS[$section];
		$comparable = [];
		foreach ($rows as $row) {
			foreach ($ignore as $column) {
				unset($row[$column]);
			}
			$comparable[] = $row;
		}
		usort($comparable, static fn(array $a, array $b): int => strcmp(
			(string)($a['guid'] ?? $a['item_guid'] ?? $a['vault_guid'] ?? ''),
			(string)($b['guid'] ?? $b['item_guid'] ?? $b['vault_guid'] ?? ''),
		));
		return $comparable;
	}

	/**
	 * @param list<array<string, mixed>> $rows
	 * @return array<string, array<string, mixed>>
	 */
	private function indexByGuid(array $rows): array {
		$indexed = [];
		foreach ($rows as $row) {
			$indexed[(string)$row['guid']] = $row;
		}
		return $indexed;
	}

	/**
	 * @param array<string, array<string, mixed>> $rowsByGuid
	 */
	private function guidOfId(array $rowsByGuid, int $id): string {
		foreach ($rowsByGuid as $guid => $row) {
			if ((int)$row['id'] === $id) {
				return $guid;
			}
		}
		self::fail('No row with id ' . $id);
	}

	/**
	 * @param list<array<string, mixed>> $rows
	 * @param array<string, mixed> $oldAcl
	 * @return array<string, mixed>
	 */
	private function findAcl(array $rows, array $oldAcl): array {
		foreach ($rows as $row) {
			if ($row['item_guid'] === $oldAcl['item_guid'] && $row['user_id'] === $oldAcl['user_id']) {
				return $row;
			}
		}
		self::fail('No restored ACL for ' . $oldAcl['item_guid']);
	}

	/**
	 * @param list<array<string, mixed>> $rows
	 * @return list<string>
	 */
	private function guids(array $rows): array {
		return array_map(static fn(array $row): string => (string)$row['guid'], $rows);
	}

	/**
	 * @return array<string, list<int>>
	 */
	private function idsForUser(string $userId): array {
		return [
			'vaults'      => $this->sortedIds($this->vaultMapper->findVaultsFromUser($userId)),
			'credentials' => $this->sortedIds($this->credentialMapper->getByUser($userId)),
			'files'       => $this->sortedIds($this->fileMapper->getFilesByUser($userId)),
			'revisions'   => $this->sortedIds($this->revisionMapper->getByUser($userId)),
			'acl'         => $this->sortedIds($this->sharingACLMapper->getByUser($userId)),
		];
	}

	/**
	 * @param list<object> $entities
	 * @return list<int>
	 */
	private function sortedIds(array $entities): array {
		$ids = array_map(static fn(object $entity): int => (int)$entity->getId(), $entities);
		sort($ids);
		return $ids;
	}
}
