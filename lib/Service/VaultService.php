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

namespace OCA\Passman\Service;

use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\VaultMapper;


class VaultService {

	private $vaultMapper;

	public function __construct(VaultMapper $vaultMapper) {
		$this->vaultMapper = $vaultMapper;
	}

	public function getByUser($userId) {
		return $this->vaultMapper->findVaultsFromUser($userId);
	}

	public function getById($vault_id, $user_id) {
		$vault = $this->vaultMapper->find($vault_id, $user_id);
		$vault = $vault[0];
		$return = array(
			'vault_id' => $vault->getId(),
			'guid' => $vault->getGuid(),
			'name' => $vault->getName(),
			'created' => $vault->getCreated(),
			'private_sharing_key' => $vault->getPrivateSharingKey(),
			'public_sharing_key' => $vault->getPublicSharingKey(),
			'sharing_keys_generated' => $vault->getSharingKeysGenerated(),
			'settings' => $vault->getSettings(),
			'last_access' => $vault->getlastAccess()
		);
		return $return;
	}

	public function createVault($vault_name, $userId) {
		return $this->vaultMapper->create($vault_name, $userId);
	}

	public function setLastAccess($vault_id, $user_id){
		return $this->vaultMapper->setLastAccess($vault_id, $user_id);
	}

	public function updateSharingKeys($vault_id, $privateKey, $publicKey){
		return $this->vaultMapper->updateSharingKeys($vault_id, $privateKey, $publicKey);
	}
}