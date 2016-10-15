<?php

/**
 *
 * Date: 15/10/16
 * Time: 20:06
 *
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
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

//	/**
//	 * @covers ::getCredentialById
//	 */
//	public function testGetCredentialById() {
//
//	}
}