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

use OCA\Passman\BackupRestore\Restore\ServerEncryptionApplier;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Service\EncryptService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(ServerEncryptionApplier::class)]
class ServerEncryptionApplierTest extends TestCase {
	private EncryptService&MockObject $encryptService;
	private ServerEncryptionApplier   $applier;

	protected function setUp(): void {
		parent::setUp();

		$this->encryptService = $this->createMock(EncryptService::class);
		$this->applier = new ServerEncryptionApplier($this->encryptService);
	}

	public function testEmptyCredentialFieldIsPassedAsEmptyStringAndRestoredAsNull(): void {
		$this->encryptService->expects($this->once())
			->method('encryptCredential')
			->with($this->callback(static function (array $data): bool {
				// EncryptService needs a string for every field: the empty field must never reach it as null
				return $data['description'] === '' && $data['username'] === 'alice';
			}))
			->willReturnArgument(0);

		$row = array_fill_keys($this->encryptService->encrypted_credential_fields, null);
		$row['guid'] = 'cred-1';
		$row['username'] = 'alice';

		$result = $this->applier->credentialRow($row);

		$this->assertNull($result['description']);
		$this->assertSame('alice', $result['username']);
		$this->assertSame('cred-1', $result['guid']);
	}

	public function testMalformedPortableRevisionThrowsInvalidBackupException(): void {
		$this->encryptService->expects($this->never())->method('encryptCredential');
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/rev-guid-1" is not an object/');

		$this->applier->revisionData('not-an-array', 'rev-guid-1');
	}
}
