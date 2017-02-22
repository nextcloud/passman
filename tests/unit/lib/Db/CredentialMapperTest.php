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

use \OCA\Passman\Db\CredentialMapper;
use \OCA\Passman\Db\Credential;
use \OCA\Passman\Utility\Utils;

/**
 * @coversDefaultClass \OCA\Passman\Db\CredentialMapper
 */
class CredentialMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_credentials'
	];

	/**
	 * @var CredentialMapper
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
		$this->mapper = new CredentialMapper($this->db, new Utils());
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
	 * @covers ::getCredentialsByVaultId
	 */
	public function testGetCredentialsByVaultId() {
		$expected = $this->findInDataset(
			self::TABLES[0],
			'vault_id',
			$this->dataset->getRow(0)['vault_id']
		);

		$expected = $this->filterDataset($expected, 'vault_id', $expected[0]['vault_id']);

		/**
		 * @var Credential[] $expected
		 */
		foreach ($expected as &$value) $value = Credential::fromRow($value);

		$data = $this->mapper->getCredentialsByVaultId(
			$expected[0]->getVaultId(),
			$expected[0]->getUserId()
		);

		$this->assertCount(count($expected), $data);
		$this->assertEquals($expected, $data);
	}

	/**
	 * @covers ::getRandomCredentialByVaultId
	 */
	public function testGetRandomCredentialByVaultId() {
		$row = $this->dataset->getRow(0);

		$data = $this->mapper->getRandomCredentialByVaultId(
			$row['vault_id'],
			$row['user_id']
		);
		$this->assertCount(1, $data);

		$tmp = $this->findInDataset(self::TABLES[0], 'vault_id', $row['vault_id']);
		$tmp = $this->filterDataset($tmp, 'user_id', $row['user_id']);
		$tmp = $this->filterDataset($tmp, 'id', $data[0]->getId());
		$this->assertCount(count($tmp), $data);

		$tmp[0] = Credential::fromRow($tmp[0]);
		$this->assertEquals($tmp, $data);
	}

	/**
	 * @covers ::getExpiredCredentials
	 */
	public function testGetExpiredCredentials() {
		$expired = [];

		for ($i = 0; $i < $this->dataset->getRowCount(); $i++) {
			$row = $this->dataset->getRow($i);
			if ($row['expire_time'] > 0 && $row['expire_time'] < Utils::getTime()){
				$expired[] = Credential::fromRow($row);
			}
		}

		$data = $this->mapper->getExpiredCredentials(Utils::getTime());
		$this->assertNotCount(0, $data, "Not any expired credentials in the dataset?");
		$this->assertCount(count($expired), $data);
		$this->assertEquals($expired, $data);
	}

	/**
	 * @covers ::getCredentialById
	 */
	public function testGetCredentialById() {
		$expected = Credential::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getCredentialById($expected->getId());
		$this->assertInstanceOf(Credential::class, $data);
		$this->assertEquals($expected, $data);

		$data = $this->mapper->getCredentialById($expected->getId(), $expected->getUserId());
		$this->assertInstanceOf(Credential::class, $data);
		$this->assertEquals($expected, $data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getCredentialById(PHP_INT_MAX);
	}

	/**
	 * @covers ::getCredentialLabelById
	 */
	public function testGetCredentialLabelById() {
		$expected = $this->dataset->getRow(0);

		$data = $this->mapper->getCredentialLabelById($expected['id']);
		$this->assertInstanceOf(Credential::class, $data);
		$this->assertSame(intval($expected['id']), $data->getId());
		$this->assertSame($expected['label'], $data->getLabel());

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getCredentialLabelById(PHP_INT_MAX);
	}

	/**
	 * @covers ::getCredentialByGUID
	 */
	public function testGetCredentialByGUID() {
		$data = Credential::fromRow($this->dataset->getRow(0));
		$result = $this->mapper->getCredentialByGUID($data->getGuid());
		$this->assertInstanceOf(Credential::class, $result);
		$this->assertEquals($data, $result);

		$result = $this->mapper->getCredentialByGUID($data->getGuid(), $data->getUserId());
		$this->assertInstanceOf(Credential::class, $result);
		$this->assertEquals($data, $result);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getCredentialByGUID("ASDF");
	}

	/**
	 * @covers ::create
	 */
	public function testCreate() {
		$raw_credential = [
			'vault_id'			=> 5,
			'user_id'			=> 'WolFi',
			'label'				=> "some label",
			'description'		=> "Some description",
			'tags'				=> "tag, tag, tags",
			'email'				=> "someone@example.com",
			'username'			=> "some_user",
			'password'			=> "some st0ng p4\$\$word",
			'url'				=> "www.example.com/login",
			'favicon'			=> "",
			'renew_interval'	=> 4,
			'expire_time'		=> Utils::getTime()+100,
			'delete_time'		=> null,
			'files'				=> "{some_file}",
			'custom_fields'		=> "{custom_fields}",
			'otp'				=> "otp_code",
			'hidden'			=> false,
			'shared_key'		=> null
		];

		$result = $this->mapper->create($raw_credential);
		$this->assertInstanceOf(Credential::class, $result);
		$this->assertNotNull($result->getId());

		$expected = Credential::fromRow($raw_credential);
		$expected->setId($result->getId());
		$expected->setCreated($result->getCreated());
		$expected->setChanged($result->getChanged());
		$expected->setGuid($result->getGuid());

		$this->assertEquals($expected->jsonSerialize(), $result->jsonSerialize());

		$data = $this->mapper->getCredentialById($expected->getId());
		$this->assertEquals($expected->jsonSerialize(), $data->jsonSerialize());
	}

	/**
	 * @covers ::updateCredential
	 */
	public function testUpdateCredential() {
		$original_row = $this->dataset->getRow(0);
		$raw_credential = [
			'guid'				=> $original_row['guid'],
			'label'				=> $original_row['label'] . "asdf",
			'description'		=> $original_row['description'] . 'fdsa',
			'tags'				=> $original_row['tags'] . ' TAG!',
			'email'				=> $original_row['email'] . 'RAWR!',
			'username'			=> $original_row['username'] . "roof",
			'password'			=> $original_row['password'] . ' NOOO, not giving my pw',
			'url'				=> $original_row['url'] . '/some/path',
			'favicon'			=> $original_row['favicon'] . "ttt",
			'renew_interval'	=> $original_row['renew_interval'] + 100,
			'expire_time'		=> $original_row['expire_time'] +500,
			'files'				=> $original_row['files'] . " files",
			'custom_fields'		=> $original_row['custom_fields'] . "custom",
			'otp'				=> $original_row['otp'] . "asdf",
			'hidden'			=> !boolval($original_row['hidden']),
			'delete_time'		=> $original_row['delete_time'] + 1500,
			'shared_key'		=> $original_row['shared_key'] . "asdf"
		];

		$updated = $this->mapper->updateCredential($raw_credential, false);
		foreach ($raw_credential as $key => $value) {
			if ($key === 'guid') continue;
			$method = 'get' . str_replace('_', '', ucwords($key, '_'));
			$this->assertEquals($value, $updated->$method());
		}

		$real = $this->mapper->getCredentialByGUID($updated->getGuid());
		foreach ($raw_credential as $key => $value) {
			if ($key === 'guid') continue;
			$method = 'get' . str_replace('_', '', ucwords($key, '_'));
			$this->assertEquals($value, $real->$method());
			$this->assertNotEquals($original_row[$key], $real->$method());
		}
	}

	/**
	 * @covers ::deleteCredential
	 */
	public function testDeleteCredential() {
		$row = Credential::fromRow($this->dataset->getRow(0));

		$this->assertEquals($row, $this->mapper->deleteCredential($row));

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getCredentialByGUID($row->getGuid());
	}

	/**
	 * @covers ::upd
	 */
	public function testUpd() {
		$cred = $this->mapper->getCredentialById($this->dataset->getRow(0)['id']);
		$cred->setUrl("ASDF");
		$this->mapper->upd($cred);

		$this->assertEquals(
			$cred->jsonSerialize(),
			$this->mapper->getCredentialById($cred->getId())->jsonSerialize()
		);
	}
}