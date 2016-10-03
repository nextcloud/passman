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

use OCA\Passman\Db\CredentialMapper;


class CredentialService {

	private $credentialMapper;

	public function __construct(CredentialMapper $credentialMapper) {
		$this->credentialMapper = $credentialMapper;
	}

	public function createCredential($credential) {
		return $this->credentialMapper->create($credential);
	}

	public function updateCredential($credential) {
		return $this->credentialMapper->updateCredential($credential);
	}
	public function upd($credential) {
		return $this->credentialMapper->upd($credential);
	}

	public function deleteCredential($credential){
		return $this->credentialMapper->deleteCredential($credential);
	}

	public function getCredentialsByVaultId($vault_id, $user_id) {
		return $this->credentialMapper->getCredentialsByVaultId($vault_id, $user_id);
	}

	public function getRandomCredentialByVaultId($vault_id, $user_id) {
		$credentials = $this->credentialMapper->getRandomCredentialByVaultId($vault_id, $user_id);
		return array_pop($credentials);
	}

	public function getExpiredCredentials($timestamp) {
		return $this->credentialMapper->getExpiredCredentials($timestamp);
	}

	public function getCredentialById($credential_id, $user_id){
        $credential = $this->credentialMapper->getCredentialById($credential_id);
        if ($credential->getUserId() == $user_id){
            return $credential;
        }
	}
	public function getCredentialLabelById($credential_id){
		return $this->credentialMapper->getCredentialLabelById($credential_id);
	}
}