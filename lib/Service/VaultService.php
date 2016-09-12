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

	public function createVault($vault_name, $userId) {
		return $this->vaultMapper->create($vault_name, $userId);
	}

	public function setLastAccess($vault_id){
		return $this->vaultMapper->setLastAccess($vault_id);
	}
}