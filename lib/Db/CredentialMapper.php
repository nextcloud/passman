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
	public function getCredentialsByVaultId($vault_id, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `user_id` = ? and vault_id = ?';
		return $this->findEntities($sql, [$user_id, $vault_id]);
	}

	public function getExpiredCredentials($timestamp){
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `expire_time` > 0 AND `expire_time` < ?';
		return $this->findEntities($sql, [$timestamp]);
	}

	public function getCredentialById($credential_id){
		$sql = 'SELECT * FROM `*PREFIX*passman_credentials` ' .
			'WHERE `id` = ?';
		return $this->findEntity($sql,[$credential_id]);
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

	public function update($raw_credential){
		if(!$raw_credential['guid']){
			$raw_credential['guid'] =  $this->utils->GUID();
		}
		if(!$raw_credential['created']){
			$raw_credential['created'] = $this->utils->getTime();
		}
		$credential = new Credential();
		$credential->setId($raw_credential['credential_id']);
		$credential->setGuid($raw_credential['guid']);
		$credential->setVaultId($raw_credential['vault_id']);
		$credential->setUserId($raw_credential['user_id']);
		$credential->setLabel($raw_credential['label']);
		$credential->setDescription($raw_credential['description']);
		$credential->setCreated($raw_credential['created']);
		$credential->setChanged($this->utils->getTime());
		$credential->setTags($raw_credential['tags']);
		$credential->setEmail($raw_credential['email']);
		$credential->setUsername($raw_credential['username']);
		$credential->setPassword($raw_credential['password']);
		$credential->setUrl($raw_credential['url']);
		$credential->setFavicon($raw_credential['favicon']);
		$credential->setRenewInterval($raw_credential['renew_interval']);
		$credential->setExpireTime($raw_credential['expire_time']);
		$credential->setFiles($raw_credential['files']);
		$credential->setCustomFields($raw_credential['custom_fields']);
		$credential->setOtp($raw_credential['otp']);
		$credential->setHidden($raw_credential['hidden']);
		$credential->setDeleteTime($raw_credential['delete_time']);
		return parent::update($credential);
	}

}