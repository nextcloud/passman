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

namespace OCA\Passman\Service;

use OCA\Passman\Db\Vault;
use OCA\Passman\Db\VaultMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;


class VaultService {

	private $vaultMapper;

	public function __construct(VaultMapper $vaultMapper) {
		$this->vaultMapper = $vaultMapper;
	}

	/**
	 * Get vaults from a user.
	 * @param $userId
	 * @return Entity[]
	 */
	public function getByUser($userId) {
		return $this->vaultMapper->findVaultsFromUser($userId);
	}

	/**
	 * Get a single vault
	 * @param $vault_id
	 * @param $user_id
	 * @return Entity[]
	 */
	public function getById($vault_id, $user_id) {
		return $this->vaultMapper->find($vault_id, $user_id);
	}

	/**
	 * Get a single vault.
	 * @param $vault_guid
	 * @param $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getByGuid($vault_guid, $user_id) {
		return $this->vaultMapper->findByGuid($vault_guid, $user_id);
	}

	/**
	 * Create a new vault.
	 * @param $vault_name
	 * @param $userId
	 * @return Vault
	 */
	public function createVault($vault_name, $userId) {
		return $this->vaultMapper->create($vault_name, $userId);
	}

	/**
	 * Update vault
	 * @param $vault
	 * @return Vault|Entity
	 */
	public function updateVault($vault) {
		return $this->vaultMapper->updateVault($vault);
	}

	/**
	 * Update last access time of a vault.
	 * @param $vault_id
	 * @param $user_id
	 * @return Vault|Entity
	 */
	public function setLastAccess($vault_id, $user_id) {
		return $this->vaultMapper->setLastAccess($vault_id, $user_id);
	}

	/**
	 * Update sharing keys of a vault.
	 * @param $vault_id
	 * @param $privateKey
	 * @param $publicKey
	 * @return Vault|Entity
	 */
	public function updateSharingKeys($vault_id, $privateKey, $publicKey) {
		return $this->vaultMapper->updateSharingKeys($vault_id, $privateKey, $publicKey);
	}

	/**
	 * Delete a vault from user
	 * @param $vault_guid
	 * @param $user_id
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function deleteVault($vault_guid, $user_id) {
		$vault = $this->getByGuid($vault_guid, $user_id);
		if ($vault instanceof Vault) {
			$this->vaultMapper->deleteVault($vault);
		}
	}
}
