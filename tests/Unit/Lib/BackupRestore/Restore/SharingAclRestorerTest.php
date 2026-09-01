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

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\Restore\EntityWriter;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestoreLookup;
use OCA\Passman\BackupRestore\Restore\RestoreWarnings;
use OCA\Passman\BackupRestore\Restore\SharingAclRestorer;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(SharingAclRestorer::class)]
class SharingAclRestorerTest extends TestCase {
	private SharingACLMapper&MockObject $sharingACLMapper;
	private RestoreLookup&MockObject    $lookup;
	private SharingAclRestorer          $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->sharingACLMapper = $this->createMock(SharingACLMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->restorer = new SharingAclRestorer(
			$this->sharingACLMapper,
			new EntityWriter(),
			$this->lookup,
		);
	}

	public function testSkipsWhenTheCredentialCannotBeResolved(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARING_ACL => [[
				'item_id'    => 2,
				'item_guid'  => 'cred-1',
				'vault_id'   => 10,
				'vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(null);
		$this->sharingACLMapper->expects($this->never())->method('insert');
		$this->sharingACLMapper->expects($this->never())->method('update');

		$this->restorer->restore($archive, $context);

		$this->assertSame(
			[RestoreWarnings::missingCredential('sharing acl entry', 'cred-1')],
			$context->result->warnings
		);
	}

	public function testSkipsWhenTheVaultCannotBeResolved(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARING_ACL => [[
				'item_id'    => 2,
				'item_guid'  => 'cred-1',
				'vault_id'   => 10,
				'vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(null);
		$this->sharingACLMapper->expects($this->never())->method('insert');

		$this->restorer->restore($archive, $context);

		$this->assertSame(
			[RestoreWarnings::missingVault('sharing acl entry', 'cred-1', 'vault-1')],
			$context->result->warnings
		);
	}

	public function testReplaceDoesNotLookUpExistingAcl(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARING_ACL => [[
				'item_id'    => 2,
				'item_guid'  => 'cred-1',
				'vault_id'   => 10,
				'vault_guid' => 'vault-1',
				'user_id'    => 'bob',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(100);
		$this->lookup->expects($this->never())->method('findExisting');
		$this->sharingACLMapper->expects($this->once())
			->method('insert')
			->with($this->callback(static function (SharingACL $entity): bool {
				return $entity->getItemId() === 20 && $entity->getVaultId() === 100;
			}))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}

	public function testMergeLooksUpExistingAcl(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARING_ACL => [[
				'item_id'    => 2,
				'item_guid'  => 'cred-1',
				'vault_id'   => 10,
				'vault_guid' => 'vault-1',
				'user_id'    => 'bob',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$existing = SharingACL::fromRow(['id' => 70]);

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(100);
		$this->lookup->expects($this->once())->method('findExisting')->willReturn($existing);
		$this->sharingACLMapper->expects($this->never())->method('insert');
		$this->sharingACLMapper->expects($this->once())
			->method('update')
			->with($this->callback(static fn(SharingACL $entity): bool => $entity->getId() === 70))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}
}
