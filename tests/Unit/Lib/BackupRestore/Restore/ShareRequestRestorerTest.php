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
use OCA\Passman\BackupRestore\Restore\ShareRequestRestorer;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(ShareRequestRestorer::class)]
class ShareRequestRestorerTest extends TestCase {
	private ShareRequestMapper&MockObject $shareRequestMapper;
	private RestoreLookup&MockObject      $lookup;
	private ShareRequestRestorer          $restorer;

	protected function setUp(): void {
		parent::setUp();

		$this->shareRequestMapper = $this->createMock(ShareRequestMapper::class);
		$this->lookup = $this->createMock(RestoreLookup::class);
		$this->restorer = new ShareRequestRestorer(
			$this->shareRequestMapper,
			new EntityWriter(),
			$this->lookup,
		);
	}

	public function testSkipsWhenTheCredentialCannotBeResolved(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARE_REQUESTS => [[
				'item_id'           => 2,
				'item_guid'         => 'cred-1',
				'target_vault_id'   => 10,
				'target_vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(null);
		$this->shareRequestMapper->expects($this->never())->method('insert');
		$this->shareRequestMapper->expects($this->never())->method('update');

		$this->restorer->restore($archive, $context);

		$this->assertSame(
			[RestoreWarnings::missingCredential('share request', 'cred-1')],
			$context->result->warnings
		);
	}

	public function testSkipsWhenTheTargetVaultCannotBeResolved(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARE_REQUESTS => [[
				'item_id'           => 2,
				'item_guid'         => 'cred-1',
				'target_vault_id'   => 10,
				'target_vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(null);
		$this->shareRequestMapper->expects($this->never())->method('insert');

		$this->restorer->restore($archive, $context);

		$this->assertSame(
			[RestoreWarnings::missingVault('share request', 'cred-1', 'vault-1')],
			$context->result->warnings
		);
	}

	public function testReplaceDoesNotLookUpExistingRequestAndRemapsIds(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARE_REQUESTS => [[
				'item_id'           => 2,
				'item_guid'         => 'cred-1',
				'target_vault_id'   => 10,
				'target_vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(100);
		$this->lookup->expects($this->never())->method('findExisting');
		$this->shareRequestMapper->expects($this->once())
			->method('insert')
			->with($this->callback(static function (ShareRequest $entity): bool {
				return $entity->getItemId() === 20 && $entity->getTargetVaultId() === 100;
			}))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}

	public function testMergeLooksUpExistingRequest(): void {
		$archive = BackupArchiveFactory::archive(sectionRows: [
			BackupArchive::SECTION_SHARE_REQUESTS => [[
				'item_id'           => 2,
				'item_guid'         => 'cred-1',
				'target_vault_id'   => 10,
				'target_vault_guid' => 'vault-1',
			]],
		]);
		$context = new RestoreContext(RestoreService::MODE_MERGE, new RestoreResult());
		$existing = ShareRequest::fromRow(['id' => 40]);

		$this->lookup->expects($this->once())->method('resolveCredentialId')->willReturn(20);
		$this->lookup->expects($this->once())->method('resolveVaultId')->willReturn(100);
		$this->lookup->expects($this->once())->method('findExisting')->willReturn($existing);
		$this->shareRequestMapper->expects($this->never())->method('insert');
		$this->shareRequestMapper->expects($this->once())
			->method('update')
			->with($this->callback(static fn(ShareRequest $entity): bool => $entity->getId() === 40))
			->willReturnArgument(0);

		$this->restorer->restore($archive, $context);
	}
}
