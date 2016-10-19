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

use \OCA\Passman\Db\SharingACLMapper;
use \OCA\Passman\Db\SharingACL;

/**
 * @coversDefaultClass \OCA\Passman\Db\SharingACLMapper
 */
class SharingACLMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_sharing_acl'
	];

	/**
	 * @var SharingACLMapper
	 */
	protected $mapper;

	/**
	 * @var PHPUnit_Extensions_Database_DataSet_ITable
	 */
	protected $dataset;
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

	public function setUp() {
		parent::setUp();
		$this->mapper = new SharingACLMapper($this->db);
		$this->dataset = $this->getTableDataset(self::TABLES[0]);
	}

	/**
	 * @covers ::getItemACL
	 * @uses \OCA\Passman\Db\SharingACL
	 */
	public function testGetItemACL() {
		$expected_acl = $this->dataset->getRow(0);
		$expected_acl = SharingACL::fromRow($expected_acl);

		$acl = $this->mapper->getItemACL(
			$expected_acl->getUserId(),
			$expected_acl->getItemGuid()
		);

		$this->assertInstanceOf(SharingACL::class, $acl);
		$this->assertEquals($expected_acl, $acl);
	}

	/**
	 * @covers ::getVaultEntries
	 */
	public function testGetVaultEntries() {
		$expected_data = $this->findInDataset(self::TABLES[0], 'user_id', $this->dataset->getRow(0)['user_id']);

		$this->assertNotCount(0, $expected_data, "The dataset has no values D:");
		$result = $this->mapper->getVaultEntries($expected_data[0]['user_id'], $expected_data[0]['vault_guid']);

		$this->assertInternalType('array', $expected_data);
		$this->assertCount(count($expected_data), $result);
		$this->assertInstanceOf(SharingACL::class, $result[0]);

		foreach ($expected_data as &$row) {
			$row = SharingACL::fromRow($row);
		}

		$this->assertEquals($expected_data, $result, "Data not matching the tests data", 0.0, 10, true, false);
	}

	/**
	 * @covers ::updateCredentialACL
	 */
	public function testUpdateCredentialACL() {
		$expected_data = SharingACL::fromRow($this->dataset->getRow(0));
		$data = $this->mapper->getItemACL(
			$expected_data->getUserId(),
			$expected_data->getItemGuid()
		);

		$this->assertEquals($expected_data, $data);

		$data->setExpire(\OCA\Passman\Utility\Utils::getTime());
		$this->mapper->updateCredentialACL($data);

		$updated = $this->mapper->getItemACL(
			$expected_data->getUserId(),
			$expected_data->getItemGuid()
		);

		$this->assertEquals($data->getId(), $updated->getId());
		$this->assertNotEquals($expected_data->getExpire(), $updated->getExpire());
		$this->assertEquals($data->getExpire(), $updated->getExpire());
	}

	/**
	 * @covers ::getCredentialAclList
	 */
	public function testGetCredentialAclList() {
		$expected_data = $this->findInDataset(
			self::TABLES[0],
			'item_guid',
			$this->dataset->getRow(0)['item_guid']
		);

		$this->assertNotEmpty($expected_data);

		$data = $this->mapper->getCredentialAclList($expected_data[0]['item_guid']);

		$this->assertInternalType('array', $data);
		$this->assertCount(count($expected_data), $data);
		$this->assertInstanceOf(SharingACL::class, $data[0]);

		foreach ($expected_data as &$row) {
			$row = SharingACL::fromRow($row);
		}

		$this->assertEquals($expected_data, $data, "Data not matching the tests data", 0.0, 10, true, false);
	}

	/**
	 * @covers ::deleteShareACL
	 */
	public function testDeleteShareACL() {
		$test_data = SharingACL::fromRow($this->dataset->getRow(0));

		$this->mapper->deleteShareACL($test_data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->mapper->getItemACL(
			$test_data->getUserId(),
			$test_data->getItemGuid()
		);
	}
}