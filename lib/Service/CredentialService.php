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

use OCA\Passman\Db\Credential;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\CredentialMapper;


class CredentialService {

	private $credentialMapper;
	private $sharingACL;

	public function __construct(CredentialMapper $credentialMapper, SharingACLMapper $sharingACL) {
		$this->credentialMapper = $credentialMapper;
		$this->sharingACL = $sharingACL;
	}

	/**
	 * Create a new credential
	 * @param $user_id
	 * @param $item_guid
	 * @return Credential
	 */
	public function createCredential($credential) {
		return $this->credentialMapper->create($credential);
	}

	public function updateCredential($credential) {
		return $this->credentialMapper->updateCredential($credential);
	}

	/**
	 * @param Credential $credential
	 */
	public function upd($credential) {
		return $this->credentialMapper->upd($credential);
	}

	/**
	 * @param Credential $credential
	 */
	public function deleteCredential($credential){
		return $this->credentialMapper->deleteCredential($credential);
	}

	/**
	 * @param integer $vault_id
	 */
	public function getCredentialsByVaultId($vault_id, $user_id) {
		return $this->credentialMapper->getCredentialsByVaultId($vault_id, $user_id);
	}

	/**
	 * @param integer $vault_id
	 */
	public function getRandomCredentialByVaultId($vault_id, $user_id) {
		$credentials = $this->credentialMapper->getRandomCredentialByVaultId($vault_id, $user_id);
		return array_pop($credentials);
	}

	/**
	 * @param integer $timestamp
	 */
	public function getExpiredCredentials($timestamp) {
		return $this->credentialMapper->getExpiredCredentials($timestamp);
	}

	public function getCredentialById($credential_id, $user_id) {
        $credential = $this->credentialMapper->getCredentialById($credential_id);
        if ($credential->getUserId() === $user_id) {
            return $credential;
        }
        else {
            $acl = $this->sharingACL->getItemACL($user_id, $credential->getGuid());
            if ($acl->hasPermission(SharingACL::READ));
            return $credential;
        }

        throw new DoesNotExistException("Did expect one result but found none when executing");
	}

	/**
	 * @param integer $credential_id
	 */
	public function getCredentialLabelById($credential_id){
		return $this->credentialMapper->getCredentialLabelById($credential_id);
	}

	public function getCredentialByGUID($credential_guid, $user_id = null){
	    return $this->credentialMapper->getCredentialByGUID($credential_guid, $user_id);
    }
}