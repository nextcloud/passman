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

use OCA\Passman\Utility\Utils;
use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class VaultMapper extends Mapper {
	private $utils;
	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, 'passman_vaults');
		$this->utils = $utils;
	}


	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return Vault[]
	 */
	public function find($vault_id, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `id`= ? and `user_id` = ?';
		return $this->findEntities($sql, [$vault_id, $user_id]);
	}
	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return Vault
	 */
	public function findByGuid($vault_guid, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `guid`= ? and `user_id` = ?';
		return $this->findEntity($sql, [$vault_guid, $user_id]);
	}


	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return Vault[]
	 */
	public function findVaultsFromUser($userId){
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `user_id` = ? ';
		$params = [$userId];
		return $this->findEntities($sql, $params);
	}

	/**
	 * Creates a vault
	 * @param $vault_name
	 * @param $userId
	 * @return Vault
	 */
	public function create($vault_name, $userId){
		$vault = new Vault();
		$vault->setName($vault_name);
		$vault->setUserId($userId);
		$vault->setGuid($this->utils->GUID());
		$vault->setCreated($this->utils->getTime());
		$vault->setLastAccess(0);
		return parent::insert($vault);
	}

	/**
	 * Update last access time of a vault
	 * @param $vault_id
	 * @param $user_id
	 */
	public function setLastAccess($vault_id, $user_id){
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setUserId($user_id);
		$vault->setLastAccess(Utils::getTime());
		$this->update($vault);
	}

	/**
	 * Update vault
	 * @param Vault $vault
	 */
	public function updateVault(Vault $vault){
		$this->update($vault);
	}

	/**
	 * Update the sharing key's
	 * @param $vault_id
	 * @param $privateKey
	 * @param $publicKey
	 */
	public function updateSharingKeys($vault_id, $privateKey, $publicKey){
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setPrivateSharingKey($privateKey);
		$vault->setPublicSharingKey($publicKey);
		$vault->setSharingKeysGenerated($this->utils->getTime());
		$this->update($vault);
	}

	/**
	 * Delete a vault
	 * @param Vault $vault
	 */
	public function deleteVault(Vault $vault){
		$this->delete($vault);
	}
}