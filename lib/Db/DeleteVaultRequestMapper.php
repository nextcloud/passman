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


use Icewind\SMB\Share;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

class DeleteVaultRequestMapper extends Mapper {
	const TABLE_NAME = 'passman_delete_vault_request';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Create a new enty in the db
	 * @param DeleteVaultRequest $request
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function createRequest(DeleteVaultRequest $request){
		return $this->insert($request);
	}

	/**
	 * Get all delete requests
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function getDeleteRequests(){
		$q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME;
		return $this->findEntities($q);
	}

	/**
	 * Get request for an vault id
	 * @param $vault_id integer The vault id
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function getDeleteRequestsForVault($vault_guid){
		$q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME .' WHERE `vault_guid` = ?';
		return $this->findEntity($q, [$vault_guid]);
	}

	/**
	 * Deletes the given delete request
	 * @param DeleteVaultRequest $request    Request to delete
	 * @return DeleteVaultRequest                 The deleted request
	 */
	public function removeDeleteVaultRequest(DeleteVaultRequest $request){
		return $this->delete($request);
	}

}