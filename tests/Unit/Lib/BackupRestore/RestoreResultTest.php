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

namespace OCA\Passman\Tests\Unit\Lib\BackupRestore;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\RestoreResult;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(RestoreResult::class)]
class RestoreResultTest extends TestCase {
	public function testTotalsSumPerSectionCounters(): void {
		$result = new RestoreResult();
		$result->countDeleted(BackupArchive::SECTION_VAULTS);
		$result->countDeleted(BackupArchive::SECTION_CREDENTIALS);
		$result->countInserted(BackupArchive::SECTION_CREDENTIALS);
		$result->countInserted(BackupArchive::SECTION_CREDENTIALS);
		$result->countUpdated(BackupArchive::SECTION_FILES);

		$this->assertSame(2, $result->totalDeleted());
		$this->assertSame(2, $result->totalInserted());
		$this->assertSame(1, $result->totalUpdated());
		$this->assertSame(0, $result->totalSkipped());
	}

	public function testSkipAppendsWarningAndIncrementsSection(): void {
		$result = new RestoreResult();

		$result->skip(BackupArchive::SECTION_REVISIONS, 'missing parent');
		$result->skip(BackupArchive::SECTION_REVISIONS, 'still missing');

		$this->assertSame(2, $result->skipped[BackupArchive::SECTION_REVISIONS]);
		$this->assertSame(2, $result->totalSkipped());
		$this->assertSame(['missing parent', 'still missing'], $result->warnings);
	}
}
