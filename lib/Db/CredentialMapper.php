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
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Credential>
 */
class CredentialMapper extends QBMapper {
	const TABLE_NAME = 'passman_credentials';

	public function __construct(
		IDBConnection $db,
		private readonly Utils $utils,
	) {
		parent::__construct($db, self::TABLE_NAME);
	}


	/**
	 * Obtains the credentials by vault id (not guid)
	 *
	 * @param string $vault_id
	 * @param string $user_id
	 * @return Credential[]
	 */
	public function getCredentialsByVaultId(string $vault_id, string $user_id): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('vault_id', $qb->createNamedParameter($vault_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Get a random credential from a vault
	 *
	 * @param string $vault_id
	 * @param string $user_id
	 * @return Credential
	 * @throws Exception
	 */
	public function getRandomCredentialByVaultId(string $vault_id, string $user_id): Credential {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('vault_id', $qb->createNamedParameter($vault_id, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->isNull('shared_key'))
			->setMaxResults(20);

		$entities = $this->findEntities($qb);
		$maxEntitiesIndex = count($entities) - 1;

		return $entities[rand(0, $maxEntitiesIndex)];
	}

	/**
	 * Get expired credentials
	 *
	 * @param int $timestamp
	 * @return Credential[]
	 */
	public function getExpiredCredentials(int $timestamp): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->gt('expire_time', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->lt('expire_time', $qb->createNamedParameter($timestamp, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * Get a credential by id.
	 * Optional user id
	 *
	 * @param int $credential_id
	 * @param string|null $user_id
	 * @return Credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialById(int $credential_id, ?string $user_id = null): Credential {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($credential_id, IQueryBuilder::PARAM_INT)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
	}

	/**
	 * Get credential label by id
	 *
	 * @param int $credential_id
	 * @return Credential partial Credential, containing only 'id' and 'label'
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialLabelById(int $credential_id): Credential {
		$qb = $this->db->getQueryBuilder();
		$qb->select(['id', 'label'])
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($credential_id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * Save credential to the database.
	 *
	 * @param $raw_credential
	 * @return Credential
	 */
	public function create($raw_credential): Credential {
		$credential = new Credential();

		$credential->setGuid($this->utils->GUID());
		$credential->setVaultId($raw_credential['vault_id']);
		$credential->setUserId($raw_credential['user_id']);
		$credential->setLabel($raw_credential['label']);
		$credential->setDescription($raw_credential['description']);
		$credential->setCreated($raw_credential['created'] && $raw_credential['created'] != 0 ? $raw_credential['created'] : $this->utils->getTime());
		$credential->setChanged($raw_credential['changed'] && $raw_credential['changed'] != 0 ? $raw_credential['changed'] : $this->utils->getTime());
		$credential->setTags($raw_credential['tags']);
		$credential->setEmail($raw_credential['email']);
		$credential->setUsername($raw_credential['username']);
		$credential->setPassword($raw_credential['password']);
		$credential->setUrl($raw_credential['url']);
		$credential->setIcon($raw_credential['icon']);
		$credential->setRenewInterval($raw_credential['renew_interval']);
		$credential->setExpireTime($raw_credential['expire_time']);
		$credential->setDeleteTime($raw_credential['delete_time']);
		$credential->setFiles($raw_credential['files']);
		$credential->setCustomFields($raw_credential['custom_fields']);
		$credential->setOtp($raw_credential['otp']);
		$credential->setHidden($raw_credential['hidden']);
		$credential->setCompromised($raw_credential['compromised']);
		if (isset($raw_credential['shared_key'])) {
			$credential->setSharedKey($raw_credential['shared_key']);
		}
		return parent::insert($credential);
	}

	/**
	 * @param array $raw_credential An array containing all the credential fields
	 * @param bool $useRawUser
	 * @return Credential The updated credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function updateCredential(array $raw_credential, bool $useRawUser): Credential {
		$original = $this->getCredentialByGUID($raw_credential['guid']);
		$uid = ($useRawUser) ? $raw_credential['user_id'] : $original->getUserId();

		$credential = new Credential();
		$credential->setId($original->getId());
		$credential->setGuid($original->getGuid());
		$credential->setVaultId($original->getVaultId());
		$credential->setUserId($uid);
		$credential->setLabel($raw_credential['label']);
		$credential->setDescription($raw_credential['description']);
		$credential->setCreated($original->getCreated());
		$credential->setChanged($this->utils->getTime());
		$credential->setTags($raw_credential['tags']);
		$credential->setEmail($raw_credential['email']);
		$credential->setUsername($raw_credential['username']);
		$credential->setPassword($raw_credential['password']);
		$credential->setUrl($raw_credential['url']);
		$credential->setIcon($raw_credential['icon']);
		$credential->setRenewInterval($raw_credential['renew_interval']);
		$credential->setExpireTime($raw_credential['expire_time']);
		$credential->setFiles($raw_credential['files']);
		$credential->setCustomFields($raw_credential['custom_fields']);
		$credential->setOtp($raw_credential['otp']);
		$credential->setHidden($raw_credential['hidden']);
		$credential->setDeleteTime($raw_credential['delete_time']);
		$credential->setCompromised($raw_credential['compromised']);

		if (isset($raw_credential['shared_key'])) {
			$credential->setSharedKey($raw_credential['shared_key']);
		}
		return parent::update($credential);
	}

	public function deleteCredential(Credential $credential): Credential {
		return $this->delete($credential);
	}

	public function upd(Credential $credential): void {
		$this->update($credential);
	}

	/**
	 * Finds a credential by the given guid
	 *
	 * @param string $credential_guid
	 * @param string|null $user_id
	 * @return Credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialByGUID(string $credential_guid, ?string $user_id = null): Credential {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('guid', $qb->createNamedParameter($credential_guid, IQueryBuilder::PARAM_STR)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
	}
}
