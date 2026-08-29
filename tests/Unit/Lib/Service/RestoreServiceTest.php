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

use OCA\Passman\BackupRestore\Restore\RestorePipeline;
use OCA\Passman\BackupRestore\Restore\RestorePreflight;
use OCA\Passman\BackupRestore\Restore\RestoreScopeCleaner;
use OCA\Passman\Service\RestoreService;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use OCP\IDBConnection;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestoreService::class)]
class RestoreServiceTest extends TestCase {
	public function testInvalidModeThrowsWithoutOpeningATransaction(): void {
		$db = $this->createMock(IDBConnection::class);
		$preflight = $this->createMock(RestorePreflight::class);
		$db->expects($this->never())->method('beginTransaction');
		$preflight->expects($this->never())->method('assertRestorable');

		$service = new RestoreService(
			$preflight,
			$this->createStub(RestoreScopeCleaner::class),
			$this->createStub(RestorePipeline::class),
			$db,
		);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/Unknown restore mode/');

		$service->restore(BackupArchiveFactory::archive(), 'nope');
	}

	public function testMergeSkipsTheScopeCleaner(): void {
		$archive = BackupArchiveFactory::archive();
		$preflight = $this->createMock(RestorePreflight::class);
		$cleaner = $this->createMock(RestoreScopeCleaner::class);
		$pipeline = $this->createMock(RestorePipeline::class);
		$db = $this->createMock(IDBConnection::class);

		$preflight->expects($this->once())->method('assertRestorable')->with($archive->manifest, false);
		$cleaner->expects($this->never())->method('clean');
		$pipeline->expects($this->once())->method('run');
		$db->expects($this->once())->method('beginTransaction');
		$db->expects($this->once())->method('commit');
		$db->expects($this->never())->method('rollBack');

		$service = new RestoreService($preflight, $cleaner, $pipeline, $db);
		$service->restore($archive, RestoreService::MODE_MERGE);
	}

	public function testReplaceCleansTheScopeBeforeThePipelineRuns(): void {
		$archive = BackupArchiveFactory::archive();
		$order = [];
		$cleaner = $this->createMock(RestoreScopeCleaner::class);
		$pipeline = $this->createMock(RestorePipeline::class);

		$cleaner->expects($this->once())
			->method('clean')
			->willReturnCallback(static function () use (&$order): void {
				$order[] = 'clean';
			});
		$pipeline->expects($this->once())
			->method('run')
			->willReturnCallback(static function () use (&$order): void {
				$order[] = 'pipeline';
			});

		$service = new RestoreService(
			$this->createStub(RestorePreflight::class),
			$cleaner,
			$pipeline,
			$this->createStub(IDBConnection::class),
		);
		$service->restore($archive, RestoreService::MODE_REPLACE, true);

		$this->assertSame(['clean', 'pipeline'], $order);
	}

	public function testExceptionRollsTheTransactionBack(): void {
		$pipeline = $this->createMock(RestorePipeline::class);
		$db = $this->createMock(IDBConnection::class);
		$pipeline->expects($this->once())->method('run')->willThrowException(new \RuntimeException('boom'));
		$db->expects($this->once())->method('beginTransaction');
		$db->expects($this->once())->method('rollBack');
		$db->expects($this->never())->method('commit');

		$service = new RestoreService(
			$this->createStub(RestorePreflight::class),
			$this->createStub(RestoreScopeCleaner::class),
			$pipeline,
			$db,
		);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('boom');

		$service->restore(BackupArchiveFactory::archive(), RestoreService::MODE_MERGE);
	}
}
