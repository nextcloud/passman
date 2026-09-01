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
use OCA\Passman\BackupRestore\Restore\CredentialRestorer;
use OCA\Passman\BackupRestore\Restore\DeleteVaultRequestRestorer;
use OCA\Passman\BackupRestore\Restore\FileRestorer;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestorePipeline;
use OCA\Passman\BackupRestore\Restore\RevisionRestorer;
use OCA\Passman\BackupRestore\Restore\ShareRequestRestorer;
use OCA\Passman\BackupRestore\Restore\SharingAclRestorer;
use OCA\Passman\BackupRestore\Restore\VaultRestorer;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestorePipeline::class)]
class RestorePipelineTest extends TestCase {
	public function testRestorersRunInBackupArchiveSectionOrder(): void {
		$order = [];
		$vault = $this->createMock(VaultRestorer::class);
		$credential = $this->createMock(CredentialRestorer::class);
		$file = $this->createMock(FileRestorer::class);
		$revision = $this->createMock(RevisionRestorer::class);
		$acl = $this->createMock(SharingAclRestorer::class);
		$shareRequest = $this->createMock(ShareRequestRestorer::class);
		$deleteVault = $this->createMock(DeleteVaultRequestRestorer::class);

		$named = [
			BackupArchive::SECTION_VAULTS => $vault,
			BackupArchive::SECTION_CREDENTIALS => $credential,
			BackupArchive::SECTION_FILES => $file,
			BackupArchive::SECTION_REVISIONS => $revision,
			BackupArchive::SECTION_SHARING_ACL => $acl,
			BackupArchive::SECTION_SHARE_REQUESTS => $shareRequest,
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS => $deleteVault,
		];
		foreach ($named as $section => $restorer) {
			$restorer->expects($this->once())
				->method('restore')
				->willReturnCallback(static function () use (&$order, $section): void {
					$order[] = $section;
				});
		}

		$pipeline = new RestorePipeline(
			$vault,
			$credential,
			$file,
			$revision,
			$acl,
			$shareRequest,
			$deleteVault,
		);
		$archive = BackupArchiveFactory::archive();
		$context = new RestoreContext(RestoreService::MODE_REPLACE, new RestoreResult());

		$pipeline->run($archive, $context);

		$this->assertSame(BackupArchive::SECTIONS, $order);
	}
}
