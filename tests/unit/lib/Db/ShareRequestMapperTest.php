<?php

/**
 *
 * Date: 12/10/16
 * Time: 19:21
 *
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
 */
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\ShareRequest;


/**
 * @coversDefaultClass OCA\Passman\Db\ShareRequestMapper
 */
class ShareRequestMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_share_request'
	];

	/**
	 * @var ShareRequestMapper
	 */
	protected $mapper;

	/**
	 * @var PHPUnit_Extensions_Database_DataSet_ITable
	 */
	protected $dataset;

	public function setUp() {
		parent::setUp();
		$this->dataset = $this->getTableDataset(self::TABLES[0]);
		$this->mapper = new ShareRequestMapper($this->db);
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
	 * @covers ::getRequestByItemAndVaultGuid
	 */
	public function testGetRequestByItemAndVaultGuid() {
		$expected = ShareRequest::fromRow($this->dataset->getRow(0));
		$data = $this->mapper->getRequestByItemAndVaultGuid(
			$expected->getItemGuid(),
			$expected->getTargetVaultGuid()
		);

		$this->assertInstanceOf(ShareRequest::class, $data);
		$this->assertInstanceOf(\OCA\Passman\Utility\PermissionEntity::class, $data);
		$this->assertSame($expected->getItemGuid(), $data->getItemGuid());
		$this->assertSame($expected->getTargetVaultGuid(), $data->getTargetVaultGuid());
	}

	/**
	 * @covers ::createRequest
	 */
	public function testCreateRequest() {
		$tmp = new ShareRequest();
		$tmp->setSharedKey('asdf');
		$tmp->setCreated(1);
		$tmp->setItemGuid('asdf');
		$tmp->setFromUserId('WolFi');
		$tmp->setPermissions(5);
		$tmp->setTargetVaultGuid("ffjj-ee-3423-edsfd");
		$tmp->setTargetUserId('Sander');
		$tmp->setTargetVaultId(5);
		$tmp->setItemId(50);
		$tmp->setSharedKey('asdfasdfasd44*.-fasf');

		$insert_data = $this->mapper->createRequest($tmp);
		$this->assertInstanceOf(ShareRequest::class, $insert_data);
		$this->assertNotNull($insert_data->getId());
		$this->assertGreaterThan(0, $insert_data->getId());

		$data = $this->mapper->getRequestByItemAndVaultGuid(
			$tmp->getItemGuid(),
			$tmp->getTargetVaultGuid()
		);
		$this->assertSame($insert_data->jsonSerialize(), $data->jsonSerialize());

		$tmp->setId($data->getId());

		$this->assertSame($tmp->jsonSerialize(), $insert_data->jsonSerialize());
	}
}