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
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class VaultMapper extends QBMapper {
	const TABLE_NAME = 'passman_vaults';
	private Utils $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, self::TABLE_NAME);
		$this->utils = $utils;
	}


	/**
	 * @param int $vault_id
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function find(int $vault_id, string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($vault_id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * @param string $vault_guid
	 * @param string $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByGuid(string $vault_guid, string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('guid', $qb->createNamedParameter($vault_guid, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}


	/**
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function findVaultsFromUser(string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Creates a vault
	 *
	 * @param string $vault_name
	 * @param string $user_id
	 * @return Vault|Entity
	 */
	public function create(string $vault_name, string $user_id) {
		$vault = new Vault();
		$vault->setName($vault_name);
		$vault->setUserId($user_id);
		$vault->setGuid($this->utils->GUID());
		$vault->setCreated($this->utils->getTime());
		$vault->setLastAccess(0);
		return parent::insert($vault);
	}

	/**
	 * Update last access time of a vault
	 *
	 * @param int $vault_id
	 * @param string $user_id
	 * @return Vault|Entity
	 */
	public function setLastAccess(int $vault_id, string $user_id) {
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setUserId($user_id);
		$vault->setLastAccess(Utils::getTime());
		return $this->update($vault);
	}

	/**
	 * Update vault
	 *
	 * @param Vault $vault
	 * @return Vault|Entity
	 */
	public function updateVault(Vault $vault) {
		return $this->update($vault);
	}

	/**
	 * Update the sharing key's
	 *
	 * @param int $vault_id
	 * @param string $privateKey
	 * @param string $publicKey
	 * @return Vault|Entity
	 */
	public function updateSharingKeys(int $vault_id, string $privateKey, string $publicKey) {
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setPrivateSharingKey($privateKey);
		$vault->setPublicSharingKey($publicKey);
		$vault->setSharingKeysGenerated($this->utils->getTime());
		return $this->update($vault);
	}

	/**
	 * Delete a vault
	 *
	 * @param Vault $vault
	 */
	public function deleteVault(Vault $vault) {
		$this->delete($vault);
	}
}
