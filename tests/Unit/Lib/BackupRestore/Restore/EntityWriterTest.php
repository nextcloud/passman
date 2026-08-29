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
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Db\DeleteVaultRequest;
use OCA\Passman\Db\DeleteVaultRequestMapper;
use OCA\Passman\Exception\InvalidBackupException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(EntityWriter::class)]
class EntityWriterTest extends TestCase {
	private DeleteVaultRequestMapper&MockObject $mapper;
	private EntityWriter                        $writer;
	private RestoreResult                       $result;

	protected function setUp(): void {
		parent::setUp();

		$this->mapper = $this->createMock(DeleteVaultRequestMapper::class);
		$this->writer = new EntityWriter();
		$this->result = new RestoreResult();
	}

	public function testUnknownColumnThrowsInvalidBackupException(): void {
		$this->mapper->expects($this->never())->method('insert');
		$this->mapper->expects($this->never())->method('update');
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/unknown column "foo_column"/');

		$this->writer->store(
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS,
			$this->mapper,
			DeleteVaultRequest::class,
			['vault_guid' => 'vault-1', 'foo_column' => 'x'],
			null,
			$this->result,
		);
	}

	public function testNullColumnIsWrittenAsExplicitNullInsteadOfBeingSkipped(): void {
		$this->mapper->expects($this->once())
			->method('insert')
			->with($this->callback(static function (DeleteVaultRequest $entity): bool {
				// a fresh entity's property is already null, so only an explicit write shows up as "updated"
				return array_key_exists('reason', $entity->getUpdatedFields()) && $entity->getReason() === null;
			}))
			->willReturnArgument(0);

		$this->writer->store(
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS,
			$this->mapper,
			DeleteVaultRequest::class,
			['vault_guid' => 'vault-1', 'reason' => null],
			null,
			$this->result,
		);

		$this->assertSame(1, $this->result->inserted[BackupArchive::SECTION_DELETE_VAULT_REQUESTS]);
	}

	public function testExistingIdUpdatesInsteadOfInserting(): void {
		$this->mapper->expects($this->never())->method('insert');
		$this->mapper->expects($this->once())
			->method('update')
			->with($this->callback(static function (DeleteVaultRequest $entity): bool {
				return $entity->getId() === 42;
			}))
			->willReturnArgument(0);

		$this->writer->store(
			BackupArchive::SECTION_DELETE_VAULT_REQUESTS,
			$this->mapper,
			DeleteVaultRequest::class,
			['vault_guid' => 'vault-1'],
			42,
			$this->result,
		);

		$this->assertSame(1, $this->result->updated[BackupArchive::SECTION_DELETE_VAULT_REQUESTS]);
		$this->assertSame(0, $this->result->inserted[BackupArchive::SECTION_DELETE_VAULT_REQUESTS]);
	}
}
