<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

use \OCA\Passman\Db\FileMapper;
use \OCA\Passman\Db\File;

/**
 * @coversDefaultClass \OCA\Passman\Db\FileMapper
 */
class FileMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_files'
	];

	/**
	 * @var FileMapper
	 */
	protected $mapper;

	/**
	 * @var PHPUnit_Extensions_Database_DataSet_ITable
	 */
	protected $dataset;

	/**
	 * @after
	 */
	public function setUp() {
		parent::setUp();
		$this->mapper = new FileMapper($this->db, new \OCA\Passman\Utility\Utils());
		$this->dataset = $this->getTableDataset(self::TABLES[0]);
	}

	/**
	 * This function should return the table name, for example
	 * for a test running on oc_passman_vaults it shall return ["oc_passman_vaults"]
	 *
	 * @internal
	 * @return string[]
	 */
	public function getTablesNames() {
		return self::TABLES;
	}

	/**
	 * @covers ::getFile
	 */
	public function testGetFile() {
		$expected = File::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getFile($expected->getId());
		$this->assertInstanceOf(File::class, $data);
		$this->assertEquals($expected, $data);

		$data = $this->mapper->getFile($expected->getId(), $expected->getUserId());
		$this->assertInstanceOf(File::class,$data);
		$this->assertEquals($expected, $data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getFile(PHP_INT_MAX, "noone");
	}

	/**
	 * @covers ::getFileByGuid
	 */
	public function testGetFileByGuid() {
		$expected = File::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getFileByGuid($expected->getGuid());
		$this->assertInstanceOf(File::class, $data);
		$this->assertEquals($expected, $data);

		$data = $this->mapper->getFileByGuid($expected->getGuid(), $expected->getUserId());
		$this->assertInstanceOf(File::class, $data);
		$this->assertEquals($expected, $data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getFileByGuid("asdf", "noone");
	}

	/**
	 * @covers ::create
	 */
	public function testCreate() {
		$file_raw['file_data']	= "some file data for testing";
		$file_raw['filename']	= "Test file";
		$file_raw['mimetype'] 	= "text/plain";
		$file_raw['size']		= count($file_raw['file_data']);

		$new_file = $this->mapper->create($file_raw, "WolFi");
		$this->assertInstanceOf(File::class, $new_file);
		$this->assertSame("WolFi", $new_file->getUserId());

		$after_insert = $this->mapper->getFileByGuid($new_file->getGuid(), $new_file->getUserId());
		$this->assertEquals($new_file->jsonSerialize(), $after_insert->jsonSerialize());
	}

	/**
	 * @covers ::deleteFile
	 */
	public function testDeleteFile() {
		$row = File::fromRow($this->dataset->getRow(0));

		$this->mapper->deleteFile($row->getId(), $row->getUserId());

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getFile($row->getId());
	}

	/**
	 * @covers ::updateFile
	 */
	public function testUpdateFile() {
		$expected = File::fromRow($this->dataset->getRow(0));
		$original_row = File::fromRow($this->dataset->getRow(0));

		$expected->setFilename($expected->getFilename() . " Altered!");
		$this->assertNotEquals($original_row, $expected);

		$dta = $this->mapper->updateFile($expected);
		$this->assertEquals($expected, $dta);

		$final = $this->mapper->getFileByGuid($expected->getGuid());
		$this->assertEquals($expected->jsonSerialize(), $final->jsonSerialize());
	}
}