<?php

/**
 *
 * Date: 15/10/16
 * Time: 18:29
 *
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
 */
use \OCA\Passman\Db\CredentialRevisionMapper;
use \OCA\Passman\Db\CredentialRevision;
use \OCA\Passman\Utility\Utils;

/**
 * @coversDefaultClass \OCA\Passman\Db\CredentialRevisionMapper
 */
class CredentialRevisionMapperTest extends DatabaseHelperTest {
	CONST TABLES = [
		'oc_passman_revisions'
	];

	/**
	 * @var CredentialRevisionMapper
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
		$this->mapper = new CredentialRevisionMapper($this->db, new Utils());
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
	 * @covers ::getRevisions
	 */
	public function testGetRevisions() {
		$expected = $this->findInDataset(
			self::TABLES[0],
			'credential_id',
			$this->dataset->getRow(0)['credential_id']
		);

		$tmp = $expected;
		foreach ($tmp as &$value) $value = CredentialRevision::fromRow($value);

		$data = $this->mapper->getRevisions($expected[0]['credential_id']);
		$this->assertEquals($tmp, $data);

		$expected = $this->filterDataset($expected, 'user_id', $expected[0]['user_id']);
		foreach ($expected as &$value) $value = CredentialRevision::fromRow($value);

		$this->mapper->getRevisions($expected[0]->getCredentialId(), $expected[0]->getUserId());
		$this->assertEquals($expected, $data);

		$data = $this->mapper->getRevisions(PHP_INT_MAX);
		$this->assertCount(0, $data);
	}

	/**
	 * @covers ::getRevision
	 */
	public function testGetRevision() {
		$expected = CredentialRevision::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getRevision($expected->getId());
		$this->assertInstanceOf(CredentialRevision::class, $data);
		$this->assertEquals($expected, $data);

		$data = $this->mapper->getRevision($expected->getId(), $expected->getUserId());
		$this->assertInstanceOf(CredentialRevision::class, $data);
		$this->assertEquals($expected, $data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getRevision(PHP_INT_MAX);
	}

	/**
	 * @covers ::create
	 */
	public function testCreate() {
		$data = $this->mapper->create("credential data stuff", "WolFi", 5, "Sander");
		$this->assertEquals("credential data stuff", json_decode(base64_decode($data->getCredentialData())));
		$this->assertEquals("Sander", $data->getEditedBy());
		$this->assertEquals("WolFi", $data->getUserId());
		$this->assertEquals(5, $data->getCredentialId());
		$this->assertNotNull($data->getId());
		$this->assertGreaterThan(0, $data->getId());

		$confirm = $this->mapper->getRevision($data->getId());
		$this->assertEquals($data->jsonSerialize(), $confirm->jsonSerialize());
	}

	public function testDeleteRevision() {
		$row = CredentialRevision::fromRow($this->dataset->getRow(0));
		$this->mapper->deleteRevision($row->getId(), $row->getUserId());

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getRevision($row->getId(), $row->getUserId());
	}
}