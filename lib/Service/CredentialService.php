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
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\CredentialMapper;


class CredentialService {

	private $credentialMapper;
	private $sharingACL;
	private $encryptService;
	private $server_key;

	public function __construct(CredentialMapper $credentialMapper, SharingACLMapper $sharingACL, EncryptService $encryptService) {
		$this->credentialMapper = $credentialMapper;
		$this->sharingACL = $sharingACL;
		$this->encryptService = $encryptService;
		$this->server_key = \OC::$server->getConfig()->getSystemValue('passwordsalt', '');
	}

	/**
	 * Create a new credential
	 *
	 * @param array $credential
	 * @return Credential
	 */
	public function createCredential($credential) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->create($credential);
	}

	/**
	 * Update credential
	 *
	 * @param $credential array | Credential
	 * @param $useRawUser bool
	 * @return Credential
	 */
	public function updateCredential($credential, $useRawUser = false) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->updateCredential($credential, $useRawUser);
	}

	/**
	 * Update credential
	 *
	 * @param $credential Credential
	 * @return Credential
	 */
	public function upd(Credential $credential) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->updateCredential($credential);
	}

	/**
	 * Delete credential
	 *
	 * @param Credential $credential
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function deleteCredential(Credential $credential) {
		return $this->credentialMapper->deleteCredential($credential);
	}

	/**
	 * Get credentials by vault id
	 *
	 * @param $vault_id
	 * @param $user_id
	 * @return \OCA\Passman\Db\Credential[]
	 */
	public function getCredentialsByVaultId($vault_id, $user_id) {
		$credentials = $this->credentialMapper->getCredentialsByVaultId($vault_id, $user_id);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return $credentials;
	}

	/**
	 * Get a random credential from given vault
	 *
	 * @param $vault_id
	 * @param $user_id
	 * @return mixed
	 */
	public function getRandomCredentialByVaultId($vault_id, $user_id) {
		$credentials = $this->credentialMapper->getRandomCredentialByVaultId($vault_id, $user_id);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return array_pop($credentials);
	}

	/**
	 * Get expired credentials.
	 *
	 * @param $timestamp
	 * @return \OCA\Passman\Db\Credential[]
	 */
	public function getExpiredCredentials($timestamp) {
		$credentials = $this->credentialMapper->getExpiredCredentials($timestamp);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return $credentials;
	}

	/**
	 * Get a single credential.
	 *
	 * @param $credential_id
	 * @param $user_id
	 * @return Credential
	 * @throws DoesNotExistException
	 */
	public function getCredentialById($credential_id, $user_id) {
		$credential = $this->credentialMapper->getCredentialById($credential_id);
		if ($credential->getUserId() === $user_id) {
			return $this->encryptService->decryptCredential($credential);
		} else {
			$acl = $this->sharingACL->getItemACL($user_id, $credential->getGuid());
			if ($acl->hasPermission(SharingACL::READ)) {
				return $this->encryptService->decryptCredential($credential);
			} else {
				throw new DoesNotExistException("Did expect one result but found none when executing");
			}
		}
	}

	/**
	 * Get credential label by credential id.
	 *
	 * @param $credential_id
	 * @return Credential
	 */
	public function getCredentialLabelById($credential_id) {
		$credential = $this->credentialMapper->getCredentialLabelById($credential_id);
		return $this->encryptService->decryptCredential($credential);
	}

	/**
	 * Get credential by guid
	 *
	 * @param $credential_guid
	 * @param null $user_id
	 * @return Credential
	 */
	public function getCredentialByGUID($credential_guid, $user_id = null) {
		$credential = $this->credentialMapper->getCredentialByGUID($credential_guid, $user_id);
		return $this->encryptService->decryptCredential($credential);
	}
}