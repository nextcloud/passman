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

	/**
	 * @after
	 */
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

	/**
	 * @covers ::getRequestsByItemGuidGroupedByUser
	 */
	public function testGetRequestsByItemGuidGroupedByUser() {
		$dataset = $this->findInDataset(
			self::TABLES[0],
			'item_guid',
			$this->dataset->getRow(0)['item_guid']
		);

		$result = $this->mapper->getRequestsByItemGuidGroupedByUser($dataset[0]['item_guid']);

		$this->assertCount(count($dataset), $result);

		foreach ($dataset as &$row) $row = ShareRequest::fromRow($row);

		$this->assertEquals($dataset, $result);
	}

	/**
	 * @covers ::getUserPendingRequests
	 */
	public function testGetUserPendingRequests() {
		$dataset = $this->findInDataset(
			self::TABLES[0],
			'target_user_id',
			$this->dataset->getRow(0)['target_user_id']
		);

		$result = $this->mapper->getUserPendingRequests($dataset[0]['target_user_id']);
		$this->assertCount(count($dataset), $result);

		foreach ($dataset as &$row) $row = ShareRequest::fromRow($row);

		$this->assertEquals($dataset, $result);
	}

	/**
	 * @covers ::cleanItemRequestsForUser
	 */
	public function testCleanItemRequestsForUser() {
		$tmp = ShareRequest::fromRow($this->dataset->getRow(0));

		// Useless confusing return type that does not match nextcloud phpdocs, not checking
		$result = $this->mapper->cleanItemRequestsForUser($tmp->getItemId(), $tmp->getTargetUserId());

		$result = $this->mapper->getUserPendingRequests($tmp->getTargetUserId());

		foreach ($result as $row) {
			if ($row->getItemId() === $tmp->getItemId()) {
				$this->fail("The user("+$row->getTargetUserId()+") still has request pointing towards the item id: " + $row->getItemId() + " all the request for this user to that item should have been deleted on this test");
				break;
			}
		}
		$this->assertTrue(true);
	}

	/**
	 * @covers ::getShareRequestById
	 */
	public function testGetShareRequestById() {
		$expected = ShareRequest::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getShareRequestById($expected->getId());
		$this->assertInstanceOf(ShareRequest::class, $data);
		$this->assertEquals($expected, $data);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getShareRequestById(PHP_INT_MAX);
	}

	/**
	 * @covers ::deleteShareRequest
	 */
	public function testDeleteShareRequest() {
		$tmp = ShareRequest::fromRow($this->dataset->getRow(0));

		$result = $this->mapper->deleteShareRequest($tmp);
		$this->assertInstanceOf(ShareRequest::class, $result);

		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
		$this->mapper->getShareRequestById($tmp->getId());
	}

	/**
	 * @covers ::getShareRequestsByItemGuid
	 */
	public function testGetShareRequestsByItemGuid() {
		$dataset = $this->findInDataset(
			self::TABLES[0],
			'item_guid',
			$this->dataset->getRow(0)['item_guid']
		);

		$data = $this->mapper->getShareRequestsByItemGuid($dataset[0]['item_guid']);
		$this->assertCount(count($dataset), $data);

		foreach ($dataset as &$row) $row = ShareRequest::fromRow($row);

		$this->assertEquals($dataset, $data);
	}

	/**
	 * @covers ::updateShareRequest
	 */
	public function testUpdateShareRequest() {
		$req = ShareRequest::fromRow($this->dataset->getRow(0));

		$data = $this->mapper->getShareRequestById($req->getId());
		$this->assertEquals($req, $data);
		$data->setTargetVaultId($data->getTargetVaultId() + 50);

		$tmp = $this->mapper->updateShareRequest($data);
		$this->assertEquals($data, $tmp);

		$tmp = $this->mapper->getShareRequestById($req->getId());
		$this->assertNotSame($req->getTargetVaultId(), $tmp->getTargetVaultId());
		$this->assertSame($data->getTargetVaultId(), $tmp->getTargetVaultId());
	}

	/**
	 * @covers ::getPendingShareRequests
	 */
	public function testGetPendingShareRequests() {
		$dataset = $this->findInDataset(
			self::TABLES[0],
			'item_guid',
			$this->dataset->getRow(0)['item_guid']
		);
		$dataset = $this->filterDataset(
			$dataset,
			'target_user_id',
			$dataset[0]['target_user_id']
		);

		$data = $this->mapper->getPendingShareRequests(
			$dataset[0]['item_guid'],
			$dataset[0]['target_user_id']
		);

		foreach ($dataset as &$row) $row = ShareRequest::fromRow($row);

		$this->assertCount(count($dataset), $data);
		$this->assertEquals($dataset, $data);
	}

	/**
	 * @TODO: Check why the fuck the dataset array gets altered when increasing permisions
	 * @covers ::updatePendingRequestPermissions
	 */
	public function testUpdatePendinRequestPermissions() {
		$dataset = $this->findInDataset(
			self::TABLES[0],
			'item_guid',
			$this->dataset->getRow(0)['item_guid']
		);
		$dataset = $this->filterDataset(
			$dataset,
			'target_user_id',
			$dataset[0]['target_user_id']
		);
		$this->mapper->updatePendingRequestPermissions(
			$dataset[0]['item_guid'],
			$dataset[0]['target_user_id'],
			$dataset[0]['permissions'] + 4
		);

		$after_update = $this->mapper->getPendingShareRequests(
			$dataset[0]['item_guid'],
			$dataset[0]['target_user_id']
		);

		$this->assertCount(count($dataset), $after_update);

		//foreach ($dataset as &$row) $row = ShareRequest::fromRow($row);

		foreach ($after_update as $row) {
			if ($dataset[0]['id'] === $row->getId()){
				$old_permission = $dataset[0]['permissions'];
				$newPermission = $row->getPermissions();
				$this->assertNotEquals($old_permission, $row->getPermissions());
				$this->assertSame($newPermission, $row->getPermissions());
				$tmp_data = $row->jsonSerialize();
				$tmp_compare = $dataset[0]->jsonSerialize();

				unset($tmp_compare['permissions']);
				unset($tmp_data['permissions']);

				$this->assertSame($tmp_compare, $tmp_data);
			}
		}
	}
}