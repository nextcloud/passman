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

use OCA\Passman\BackupRestore\BackupRow;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(BackupRow::class)]
class BackupRowTest extends TestCase {
	public function testReadStringReturnsNullForMissingEmptyOrArray(): void {
		$this->assertNull(BackupRow::readString([], 'guid'));
		$this->assertNull(BackupRow::readString(['guid' => null], 'guid'));
		$this->assertNull(BackupRow::readString(['guid' => ''], 'guid'));
		$this->assertNull(BackupRow::readString(['guid' => ['nested']], 'guid'));
	}

	public function testReadStringCastsScalars(): void {
		$this->assertSame('vault-1', BackupRow::readString(['guid' => 'vault-1'], 'guid'));
		$this->assertSame('12', BackupRow::readString(['id' => 12], 'id'));
	}

	public function testReadIntReturnsNullForMissingEmptyOrNonNumeric(): void {
		$this->assertNull(BackupRow::readInt([], 'id'));
		$this->assertNull(BackupRow::readInt(['id' => null], 'id'));
		$this->assertNull(BackupRow::readInt(['id' => ''], 'id'));
		$this->assertNull(BackupRow::readInt(['id' => 'abc'], 'id'));
		$this->assertNull(BackupRow::readInt(['id' => ['1']], 'id'));
		$this->assertNull(BackupRow::readInt(['id' => [1]], 'id'));
	}

	public function testReadIntAcceptsNumericStrings(): void {
		$this->assertSame(10, BackupRow::readInt(['id' => '10'], 'id'));
		$this->assertSame(10, BackupRow::readInt(['id' => 10], 'id'));
	}
}
