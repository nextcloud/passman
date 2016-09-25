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

class VaultMapper extends Mapper {
	private $utils;
	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, 'passman_vaults');
		$this->utils = $utils;
	}


	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function find($vault_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `user_id` = ?';
		return $this->findEntities($sql, [$vault_id]);
	}

	public function findVaultsFromUser($userId){
		$sql = 'SELECT * FROM `*PREFIX*passman_vaults` ' .
			'WHERE `user_id` = ? ';
		$params = [$userId];
		return $this->findEntities($sql, $params);
	}

	public function create($vault_name, $userId){
		$vault = new Vault();
		$vault->setName($vault_name);
		$vault->setUserId($userId);
		$vault->setGuid($this->utils->GUID());
		$vault->setCreated($this->utils->getTime());
		$vault->setlastAccess(0);
		return parent::insert($vault);
	}

	public function setLastAccess($vault_id){
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setlastAccess(time());
		$this->update($vault);
	}

	public function updateSharingKeys($vault_id, $privateKey, $publicKey){
		$vault = new Vault();
		$vault->setId($vault_id);
		$vault->setPrivateSharingKey($privateKey);
		$vault->setPublicSharingKey($publicKey);
		$vault->setSharingKeysGenerated($this->utils->getTime());
		$this->update($vault);
	}
}