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
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\BackupSerializer;
use OCA\Passman\Exception\InvalidBackupException;
use OCA\Passman\Tests\Unit\Support\BackupArchiveFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(BackupManifest::class)]
class BackupManifestTest extends TestCase {
	public function testIsPortableFollowsEncryptionMode(): void {
		$portable = BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_PORTABLE);
		$raw = BackupArchiveFactory::manifest(encryptionMode: BackupManifest::MODE_RAW);

		$this->assertTrue($portable->isPortable());
		$this->assertFalse($raw->isPortable());
	}

	public function testFromArrayRejectsUnknownScope(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unknown backup scope/');

		BackupManifest::fromArray($this->manifestArray(scope: 'nope'));
	}

	public function testFromArrayRejectsUnknownEncryptionMode(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unknown encryption mode/');

		BackupManifest::fromArray($this->manifestArray(encryptionMode: 'nope'));
	}

	public function testFromArrayRejectsUnknownFormatVersion(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unsupported backup format version/');

		BackupManifest::fromArray($this->manifestArray(formatVersion: 0));
	}

	public function testFromArrayRejectsMissingChecksum(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/missing its checksum/');

		BackupManifest::fromArray($this->manifestArray(checksum: ''));
	}

	public function testChecksumIgnoresStoredJsonKeyOrder(): void {
		$archive = BackupArchiveFactory::archive(
			sectionRows: [
				BackupArchive::SECTION_VAULTS => [['id' => 1, 'guid' => 'vault-1']],
			],
		);
		$encoded = BackupArchiveFactory::encode($archive);
		$data = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);
		$data['manifest'] = array_reverse($data['manifest'], true);
		$reordered = json_encode($data, BackupSerializer::JSON_ENCODE_DEFAULT_FLAGS);

		$decoded = (new BackupSerializer())->decode($reordered);

		$this->assertTrue($decoded->manifest->validateArchiveChecksum($decoded));
		$this->assertSame($data['manifest']['checksum'], $decoded->manifest->checksum);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function manifestArray(
		int    $formatVersion = BackupManifest::FORMAT_VERSION,
		string $scope = BackupManifest::SCOPE_INSTANCE,
		string $encryptionMode = BackupManifest::MODE_PORTABLE,
		string $checksum = 'abc',
	): array {
		return [
			'format_version' => $formatVersion,
			'app_version' => '2.6.1',
			'created_at' => 0,
			'scope' => $scope,
			'encryption_mode' => $encryptionMode,
			'instance_id' => 'test-instance',
			'target_user_id' => null,
			'target_vault_guid' => null,
			'counts' => [],
			'checksum' => $checksum,
		];
	}
}
