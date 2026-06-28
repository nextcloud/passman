<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

namespace OCA\Passman\Tests\Unit\Db;

use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Tests\Unit\Support\DbTestTrait;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[Group(name: 'DB')]
#[CoversClass(FileMapper::class)]
class FileMapperTest extends TestCase {
	use DbTestTrait;

	private const TEST_USER = 'passman_file_mapper_test';

	private IDBConnection $db;
	private FileMapper    $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->db = Server::get(IDBConnection::class);
		$this->mapper = new FileMapper($this->db, new Utils());
		$this->resetData();
	}

	protected function tearDown(): void {
		$this->resetData();
		parent::tearDown();
	}

	private function resetData(): void {
		$this->deletePassmanRows($this->db, 'passman_files', 'user_id', self::TEST_USER);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function sampleFileData(array $overrides = []): array {
		$fileData = 'some file data for testing';
		return array_merge([
			'file_data' => $fileData,
			'filename'  => 'Test file',
			'mimetype'  => 'text/plain',
			'size'      => strlen($fileData),
		], $overrides);
	}

	public function testClassType(): void {
		$this->assertInstanceOf(QBMapper::class, $this->mapper);
	}

	/**
	 * @covers ::create
	 * @covers ::getFile
	 */
	public function testCreateAndGetFile(): void {
		$created = $this->mapper->create($this->sampleFileData(), self::TEST_USER);

		$this->assertInstanceOf(File::class, $created);
		$this->assertSame(self::TEST_USER, $created->getUserId());

		$byId = $this->mapper->getFile($created->getId());
		$this->assertEquals($created->jsonSerialize(), $byId->jsonSerialize());

		$byIdAndUser = $this->mapper->getFile($created->getId(), self::TEST_USER);
		$this->assertEquals($created->jsonSerialize(), $byIdAndUser->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getFile(PHP_INT_MAX, 'noone');
	}

	public function testGetFileByGuid(): void {
		$created = $this->mapper->create($this->sampleFileData(), self::TEST_USER);

		$byGuid = $this->mapper->getFileByGuid($created->getGuid());
		$this->assertEquals($created->jsonSerialize(), $byGuid->jsonSerialize());

		$byGuidAndUser = $this->mapper->getFileByGuid($created->getGuid(), self::TEST_USER);
		$this->assertEquals($created->jsonSerialize(), $byGuidAndUser->jsonSerialize());

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getFileByGuid('missing-guid', 'noone');
	}

	public function testDeleteFile(): void {
		$created = $this->mapper->create($this->sampleFileData(), self::TEST_USER);

		$this->mapper->deleteFile($created->getId(), self::TEST_USER);

		$this->expectException(DoesNotExistException::class);
		$this->mapper->getFile($created->getId());
	}

	public function testUpdateFile(): void {
		$created = $this->mapper->create($this->sampleFileData(), self::TEST_USER);
		$created->setFilename($created->getFilename() . ' Altered!');

		$updated = $this->mapper->updateFile($created);
		$this->assertSame($created->getFilename(), $updated->getFilename());

		$fromDb = $this->mapper->getFileByGuid($created->getGuid());
		$this->assertEquals($created->jsonSerialize(), $fromDb->jsonSerialize());
	}
}
