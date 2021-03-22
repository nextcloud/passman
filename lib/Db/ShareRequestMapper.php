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

namespace OCA\Passman\Db;


use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\IResult;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShareRequestMapper extends QBMapper {
	const TABLE_NAME = 'passman_share_request';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * @param ShareRequest $request
	 * @return ShareRequest|Entity
	 */
	public function createRequest(ShareRequest $request) {
		return $this->insert($request);
	}

	/**
	 * Obtains a request by the given item and vault GUID pair
	 *
	 * @param string $item_guid
	 * @param string $target_vault_guid
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getRequestByItemAndVaultGuid(string $item_guid, string $target_vault_guid) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('target_vault_guid', $qb->createNamedParameter($target_vault_guid, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * Get shared items for the given item_guid
	 *
	 * @param string $item_guid
	 * @return Entity[]
	 * @throws Exception
	 */
	public function getRequestsByItemGuidGroupedByUser(string $item_guid) {
		if (strtolower($this->db->getDatabasePlatform()->getName()) === 'mysql') {
			$this->db->executeQuery("SET sql_mode = '';");
		}
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)))
			->groupBy('target_user_id');

		return $this->findEntities($qb);
	}

	/**
	 * Deletes all pending requests for the given user to the given item
	 *
	 * @param int $item_id
	 * @param string $target_user_id
	 * @return int|IResult
	 * @throws Exception
	 */
	public function cleanItemRequestsForUser(int $item_id, string $target_user_id) {
		$qb = $this->db->getQueryBuilder();
		return $qb->delete(self::TABLE_NAME)
			->where($qb->expr()->eq('item_id', $qb->createNamedParameter($item_id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('target_user_id', $qb->createNamedParameter($target_user_id, IQueryBuilder::PARAM_STR)))
			->execute();
	}

	/**
	 * Obtains all pending share requests for the given user ID
	 *
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function getUserPendingRequests(string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('target_user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Deletes the given share request
	 * @param ShareRequest $shareRequest Request to delete
	 * @return ShareRequest                 The deleted request
	 */
	public function deleteShareRequest(ShareRequest $shareRequest) {
		return $this->delete($shareRequest);
	}

	/**
	 * Gets a share request by it's unique incremental id
	 *
	 * @param int $id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getShareRequestById(int $id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * Gets all share requests by a given item GUID
	 *
	 * @param string $item_guid
	 * @return Entity[]
	 */
	public function getShareRequestsByItemGuid(string $item_guid) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Updates the given share request,
	 * @param ShareRequest $shareRequest
	 * @return ShareRequest
	 */
	public function updateShareRequest(ShareRequest $shareRequest) {
		return $this->update($shareRequest);
	}

	/**
	 * Finds pending requests sent to the given user to the given item.
	 *
	 * @param string $item_guid
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function getPendingShareRequests(string $item_guid, string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('target_user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Updates all pending requests with the given permissions
	 *
	 * @param string $item_guid The item for which to update the requests
	 * @param string $user_id The user for which to update the requests
	 * @param int $permissions The new permissions to apply
	 * @return int|IResult
	 * @throws Exception
	 */
	public function updatePendingRequestPermissions(string $item_guid, string $user_id, int $permissions) {
		$qb = $this->db->getQueryBuilder();
		return $qb->update(self::TABLE_NAME)
			->set('permissions', $qb->createNamedParameter($permissions, IQueryBuilder::PARAM_INT))
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('target_user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)))
			->execute();
	}
}
