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
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class DeleteVaultRequestMapper extends QBMapper {
	const TABLE_NAME = 'passman_delete_vault_request';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Create a new enty in the db
	 * @param DeleteVaultRequest $request
	 * @return Entity
	 */
	public function createRequest(DeleteVaultRequest $request) {
		return $this->insert($request);
	}

	/**
	 * Get all delete requests
	 * @return Entity[]
	 */
	public function getDeleteRequests() {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME);

		return $this->findEntities($qb);
	}

	/**
	 * Get request for a vault guid
	 * @param string $vault_guid
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getDeleteRequestsForVault(string $vault_guid) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('vault_guid', $qb->createNamedParameter($vault_guid, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * Deletes the given delete request
	 * @param DeleteVaultRequest $request Request to delete
	 * @return DeleteVaultRequest         The deleted request
	 */
	public function removeDeleteVaultRequest(DeleteVaultRequest $request) {
		return $this->delete($request);
	}

}
