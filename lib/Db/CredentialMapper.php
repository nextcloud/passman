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

class CredentialMapper extends Mapper {
	private $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, 'passman_credentials');
		$this->utils = $utils;
	}


	/**
	 * Obtains the credentials by vault id (not guid)
	 *
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return Credential[]
	 */
	public function getCredentialsByVaultId($vault_id, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `user_id` = ? and vault_id = ?';
		return $this->findEntities($sql, [$user_id, $vault_id]);
	}

	/**
	 * Get a random credentail from a vault
	 *
	 * @param $vault_id
	 * @param $user_id
	 * @return Credential
	 */
	public function getRandomCredentialByVaultId($vault_id, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `user_id` = ? and vault_id = ? AND shared_key is NULL LIMIT 20';
		$entities = $this->findEntities($sql, [$user_id, $vault_id]);
		$count = count($entities) - 1;
		$entities = array_splice($entities, rand(0, $count), 1);
		return $entities;
	}

	/**
	 * Get expired credentials
	 *
	 * @param $timestamp
	 * @return Credential[]
	 */
	public function getExpiredCredentials($timestamp) {
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `expire_time` > 0 AND `expire_time` < ?';
		return $this->findEntities($sql, [$timestamp]);
	}

	/**
	 * Get an credential by id.
	 * Optional user id
	 *
	 * @param $credential_id
	 * @param null $user_id
	 * @return Credential
	 */
	public function getCredentialById($credential_id, $user_id = null) {
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `id` = ?';
		// If we want to check the owner, add it to the query
		$params = [$credential_id];
		if ($user_id !== null) {
			$sql .= ' and `user_id` = ? ';
			array_push($params, $user_id);
		}
		return $this->findEntity($sql, $params);
	}

	/**
	 * Get credential label by id
	 *
	 * @param $credential_id
	 * @return Credential
	 */
	public function getCredentialLabelById($credential_id) {
		$sql = 'SELECT id, label FROM `*PREFIX*passman_credentials` ' .
			'WHERE `id` = ? ';
		return $this->findEntity($sql, [$credential_id]);
	}

	/**
	 * Save credential to the database.
	 *
	 * @param $raw_credential
	 * @return Credential
	 */
	public function create($raw_credential) {
		$credential = new Credential();

		$credential->setGuid($this->utils->GUID());
		$credential->setVaultId($raw_credential['vault_id']);
		$credential->setUserId($raw_credential['user_id']);
		$credential->setLabel($raw_credential['label']);
		$credential->setDescription($raw_credential['description']);
		$credential->setCreated($this->utils->getTime());
		$credential->setChanged($this->utils->getTime());
		$credential->setTags($raw_credential['tags']);
		$credential->setEmail($raw_credential['email']);
		$credential->setUsername($raw_credential['username']);
		$credential->setPassword($raw_credential['password']);
		$credential->setUrl($raw_credential['url']);
		$credential->setFavicon($raw_credential['favicon']);
		$credential->setRenewInterval($raw_credential['renew_interval']);
		$credential->setExpireTime($raw_credential['expire_time']);
		$credential->setDeleteTime($raw_credential['delete_time']);
		$credential->setFiles($raw_credential['files']);
		$credential->setCustomFields($raw_credential['custom_fields']);
		$credential->setOtp($raw_credential['otp']);
		$credential->setHidden($raw_credential['hidden']);
		if (isset($raw_credential['shared_key'])) {
			$credential->setSharedKey($raw_credential['shared_key']);
		}
		return parent::insert($credential);
	}

	/**
	 * Update a credential
	 *
	 * @param $raw_credential array An array containing all the credential fields
	 * @param $useRawUser bool
	 * @return Credential The updated credential
	 */
	public function updateCredential($raw_credential, $useRawUser) {
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

		if (isset($raw_credential['shared_key'])) {
			$credential->setSharedKey($raw_credential['shared_key']);
		}
		return parent::update($credential);
	}

	public function deleteCredential(Credential $credential) {
		return $this->delete($credential);
	}

	public function upd(Credential $credential) {
		$this->update($credential);
	}

	/**
	 * Finds a credential by the given guid
	 *
	 * @param $credential_guid
	 * @return Credential
	 */
	public function getCredentialByGUID($credential_guid, $user_id = null) {
		$q = 'SELECT * FROM `*PREFIX*passman_credentials` WHERE guid = ? ';
		$params = [$credential_guid];
		if ($user_id !== null) {
			$q .= ' and `user_id` = ? ';
			array_push($params, $user_id);
		}
		return $this->findEntity($q, $params);
	}
}