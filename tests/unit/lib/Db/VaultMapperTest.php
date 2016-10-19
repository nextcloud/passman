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

use \OCA\Passman\Db\VaultMapper;
use \OCA\Passman\Utility\Utils;
use \OCA\Passman\Db\Vault;

/**
 * Unit tests for VaultMapper
 * @coversDefaultClass \OCA\Passman\Db\VaultMapper
 */
class VaultMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_vaults'
	];

	/**
	 * @var VaultMapper
	 */
	protected $vault_mapper;

	/**
	 * @var PHPUnit_Extensions_Database_DataSet_ITable
	 */
	protected $vaults_dataset;

	public function setUp() {
		parent::setUp();
		$this->vault_mapper = new VaultMapper($this->db, new Utils());
		$this->vaults_dataset = $this->getTableDataset(self::TABLES[0]);
	}

	/**
	 * @inheritdoc
	 * @return string[]
	 */
	public function getTablesNames() {
		return self::TABLES;
	}

	/**
	 * @coversNothing
	 */
	public function testClassType() {
		$this->assertInstanceOf(\OCP\AppFramework\Db\Mapper::class, $this->vault_mapper);
	}

	/**
	 * @covers ::findByGuid
	 */
	public function testFindByGuid() {
		$expected_data = $this->vaults_dataset->getRow(0);

		$data = $this->vault_mapper->findByGuid($expected_data['guid'], $expected_data['user_id']);
		$this->assertInstanceOf(Vault::class, $data);

		$this->assertEquals($expected_data['guid'], $data->getGuid());

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->vault_mapper->findByGuid('asdf', 'fdsa');
	}

	/**
	 * @covers ::findVaultsFromUser
	 */
	public function testFindVaultsFromUser() {
		$expected_row = $this->vaults_dataset->getRow(0);
		$user = $expected_row['user_id'];
		$expected_rows = $this->findInDataset(
			self::TABLES[0],
			'user_id',
			$expected_row['user_id']
		);
		$expected_rows = count($expected_rows);

		$data = $this->vault_mapper->findVaultsFromUser($user);

		$this->assertCount($expected_rows, $data);
		$this->assertInstanceOf(Vault::class, $data[0]);
		$this->assertEquals($user, $data[0]->getUserId());
	}

	/**
	 * @covers ::updateVault
	 */
	public function testUpdateVault() {
		$row = $this->vaults_dataset->getRow(0);
		$db_row = $this->vault_mapper->findByGuid($row['guid'], $row['user_id']);
		$db_row->setName("ASDF");

		$this->vault_mapper->updateVault($db_row);
		$new_data = $this->vault_mapper->findByGuid($db_row->getGuid(), $db_row->getUserId());

		$this->assertEquals($db_row->jsonSerialize(), $new_data->jsonSerialize());
		$this->assertEquals("ASDF", $new_data->getName());
		$this->assertNotEquals($row['name'], $new_data->getName());
	}

	/**
	 * @covers ::setLastAccess
	 */
	public function testSetLastAccess() {
		$row = $this->vaults_dataset->getRow(0);
		$time = Utils::getTime();

		$this->vault_mapper->setLastAccess($row['id'], $row['user_id']);
		$data = $this->vault_mapper->findByGuid($row['guid'], $row['user_id']);
		$this->assertNotEquals($row['last_access'], $data->getLastAccess());
		$this->assertEquals($time, $data->getLastAccess());
	}

	/**
	 * @covers ::create
	 */
	public function testCreate() {
		$vault_name = 'test vault name';
		$vault_user = 'WolFi';

		$vault = $this->vault_mapper->create($vault_name, $vault_user);
		$vaults_in_db = $this->vault_mapper->findVaultsFromUser($vault_user);

		$this->assertInstanceOf(Vault::class, $vault);
		$this->assertEquals($vault_name, $vault->getName());
		$this->assertEquals($vault_user, $vault->getUserId());


		$this->assertCount(1, $vaults_in_db);
		$this->assertEquals($vault->jsonSerialize(), $vaults_in_db[0]->jsonSerialize());
	}

	/**
	 * @covers ::updateSharingKeys
	 */
	public function testUpdateSharingKeys(){
		$private_key = "a private key";
		$public_key = "a public key";

		$row = $this->vaults_dataset->getRow(0);

		$this->vault_mapper->updateSharingKeys($row['id'], $private_key, $public_key);
		$data = $this->vault_mapper->findByGuid($row['guid'], $row['user_id']);

		$this->assertEquals($private_key, $data->getPrivateSharingKey());
		$this->assertEquals($public_key, $data->getPublicSharingKey());
	}
}
