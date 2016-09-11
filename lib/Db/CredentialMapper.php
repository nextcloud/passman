<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
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
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find($vault_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `user_id` = ? LIMIT 1';
		return $this->findEntity($sql, [$vault_id]);
	}

	public function findVaultsFromUser($userId){
		$sql = 'SELECT id, name, created, guid, last_access FROM `*PREFIX*passman_vaults` ' .
			'WHERE `user_id` = ? ';
		$params = [$userId];
		return $this->findEntities($sql, $params);
	}

	public function create($raw_credential){
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
		return parent::insert($credential);
	}

}