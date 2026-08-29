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
use OCA\Passman\BackupRestore\ScopeSelection;
use OCA\Passman\Db\Vault;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(ScopeSelection::class)]
class ScopeSelectionTest extends TestCase {
	public function testUniqueByIdKeepsOneEntityPerId(): void {
		$first = Vault::fromRow(['id' => 1, 'name' => 'first']);
		$duplicate = Vault::fromRow(['id' => 1, 'name' => 'second']);
		$other = Vault::fromRow(['id' => 2, 'name' => 'other']);

		$unique = ScopeSelection::uniqueById([[$first], [$duplicate, $other]]);

		$this->assertCount(2, $unique);
		// last with the same id wins
		$this->assertSame('second', $unique[0]->getName());
		$this->assertSame('other', $unique[1]->getName());
	}

	public function testEntitiesReturnsTheMatchingSectionList(): void {
		$vault = Vault::fromRow(['id' => 1, 'name' => 'Personal']);
		$selection = new ScopeSelection(vaults: [$vault]);

		$this->assertSame([$vault], $selection->entities(BackupArchive::SECTION_VAULTS));
		$this->assertSame([], $selection->entities(BackupArchive::SECTION_FILES));
	}

	public function testEntitiesThrowsOnUnknownSection(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches('/Unknown backup section/');

		(new ScopeSelection())->entities('nope');
	}
}
