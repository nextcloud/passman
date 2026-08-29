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

#[CoversClass(BackupSerializer::class)]
class BackupSerializerTest extends TestCase {
	private BackupSerializer $serializer;

	protected function setUp(): void {
		parent::setUp();
		$this->serializer = new BackupSerializer();
	}

	public function testEncodeDecodeRoundTripPreservesRowsAndChecksum(): void {
		$archive = BackupArchiveFactory::archive(
			sectionRows: [
				BackupArchive::SECTION_VAULTS => [[
					'id' => 1,
					'guid' => 'vault-1',
					'name' => 'Personal',
				]],
			],
		);

		$decoded = $this->serializer->decode($this->serializer->encode($archive));

		$this->assertSame('vault-1', $decoded->section(BackupArchive::SECTION_VAULTS)->rows[0]['guid']);
		$this->assertNotEmpty($decoded->manifest->checksum);
		$this->assertTrue($decoded->manifest->validateArchiveChecksum($decoded));
	}

	public function testInvalidJsonThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/not valid JSON/');

		$this->serializer->decode('{');
	}

	public function testMissingManifestThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/missing its manifest/');

		$this->serializer->decode('{"sections":{}}');
	}

	public function testChecksumMismatchThrowsInvalidBackupException(): void {
		$json = BackupArchiveFactory::encode(BackupArchiveFactory::archive(
			sectionRows: [
				BackupArchive::SECTION_VAULTS => [['id' => 1, 'guid' => 'vault-1']],
			],
		));
		$tampered = str_replace('vault-1', 'vault-tampered', $json);

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/do not match its checksum/');

		$this->serializer->decode($tampered);
	}

	public function testUnknownScopeThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unknown backup scope/');

		$this->serializer->decode($this->artifactJson(scope: 'nope'));
	}

	public function testUnknownEncryptionModeThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unknown encryption mode/');

		$this->serializer->decode($this->artifactJson(encryptionMode: 'nope'));
	}

	public function testUnknownFormatVersionThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/Unsupported backup format version/');

		$this->serializer->decode($this->artifactJson(formatVersion: 0));
	}

	public function testMissingChecksumThrowsInvalidBackupException(): void {
		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/missing its checksum/');

		$this->serializer->decode($this->artifactJson(checksum: ''));
	}

	public function testSectionThatIsNotAListThrowsInvalidBackupException(): void {
		$data = json_decode($this->artifactJson(), true);
		$data['sections'][BackupArchive::SECTION_VAULTS] = 'not-a-list';

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/must be a list of rows/');

		$this->serializer->decode(json_encode($data, BackupSerializer::JSON_ENCODE_DEFAULT_FLAGS));
	}

	public function testNonObjectRowThrowsInvalidBackupException(): void {
		$data = json_decode($this->artifactJson(), true);
		$data['sections'][BackupArchive::SECTION_VAULTS] = ['not-an-object'];

		$this->expectException(InvalidBackupException::class);
		$this->expectExceptionMessageMatches('/non-object row/');

		$this->serializer->decode(json_encode($data, BackupSerializer::JSON_ENCODE_DEFAULT_FLAGS));
	}

	/**
	 * @param mixed $checksum
	 */
	private function artifactJson(
		int    $formatVersion = BackupManifest::FORMAT_VERSION,
		string $scope = BackupManifest::SCOPE_INSTANCE,
		string $encryptionMode = BackupManifest::MODE_PORTABLE,
		mixed  $checksum = 'abc',
	): string {
		return json_encode([
			'manifest' => [
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
			],
			'sections' => array_fill_keys(BackupArchive::SECTIONS, []),
		], BackupSerializer::JSON_ENCODE_DEFAULT_FLAGS);
	}
}
