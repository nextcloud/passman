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
        if ($credential->getUserId() === $user_id){
            return $credential;
        }
        else {
            $acl = $this->sharingACL->getItemACL($user_id, $credential->getGuid());
            if ($acl->hasPermission(SharingACL::READ));
            return $credential;
        }

        throw new DoesNotExistException("Did expect one result but found none when executing");
	}
	public function getCredentialLabelById($credential_id){
		return $this->credentialMapper->getCredentialLabelById($credential_id);
	}

	public function getCredentialByGUID($credential_guid, $user_id = null){
	    return $this->credentialMapper->getCredentialByGUID($credential_guid, $user_id);
    }
}