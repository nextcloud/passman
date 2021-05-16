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
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;


class CredentialService {

	private CredentialMapper $credentialMapper;
	private SharingACLMapper $sharingACL;
	private ShareService $shareService;
	private EncryptService $encryptService;
	private $server_key;

	public function __construct(CredentialMapper $credentialMapper, SharingACLMapper $sharingACL, ShareService $shareService, EncryptService $encryptService, IConfig $config) {
		$this->credentialMapper = $credentialMapper;
		$this->sharingACL = $sharingACL;
		$this->shareService = $shareService;
		$this->encryptService = $encryptService;
		$this->server_key = $config->getSystemValue('passwordsalt', '');
	}

	/**
	 * Create a new credential
	 *
	 * @param array $credential
	 * @return Credential
	 * @throws \Exception
	 */
	public function createCredential(array $credential) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->create($credential);
	}

	/**
	 * Update credential
	 *
	 * @param array $credential
	 * @param false $useRawUser
	 * @return Credential|Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function updateCredential(array $credential, $useRawUser = false) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->updateCredential($credential, $useRawUser);
	}

	/**
	 * Update credential
	 *
	 * @param Credential $credential
	 * @return Credential|Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function upd(Credential $credential) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialMapper->updateCredential($credential->jsonSerialize(), false);
	}

	/**
	 * Delete credential
	 *
	 * @param Credential $credential
	 * @return Entity
	 */
	public function deleteCredential(Credential $credential) {
		$this->shareService->unshareCredential($credential->getGuid());
		return $this->credentialMapper->deleteCredential($credential);
	}

	/**
	 * Get credentials by vault id
	 *
	 * @param int $vault_id
	 * @param string $user_id
	 * @return Entity[]
	 * @throws \Exception
	 */
	public function getCredentialsByVaultId(int $vault_id, string $user_id) {
		$credentials = $this->credentialMapper->getCredentialsByVaultId($vault_id, $user_id);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return $credentials;
	}

	/**
	 * Get a random credential from given vault
	 *
	 * @param int $vault_id
	 * @param string $user_id
	 * @return mixed
	 */
	public function getRandomCredentialByVaultId(int $vault_id, string $user_id) {
		$credentials = $this->credentialMapper->getRandomCredentialByVaultId($vault_id, $user_id);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return array_pop($credentials);
	}

	/**
	 * Get expired credentials.
	 *
	 * @param int $timestamp
	 * @return Entity[]
	 * @throws \Exception
	 */
	public function getExpiredCredentials(int $timestamp) {
		$credentials = $this->credentialMapper->getExpiredCredentials($timestamp);
		foreach ($credentials as $index => $credential) {
			$credentials[$index] = $this->encryptService->decryptCredential($credential);
		}
		return $credentials;
	}

	/**
	 * Get a single credential.
	 *
	 * @param int $credential_id
	 * @param string $user_id
	 * @return array|Credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialById(int $credential_id, string $user_id) {
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
	 * @param int $credential_id
	 * @return array|Credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialLabelById(int $credential_id) {
		$credential = $this->credentialMapper->getCredentialLabelById($credential_id);
		return $this->encryptService->decryptCredential($credential);
	}

	/**
	 * Get credential by guid
	 *
	 * @param string $credential_guid
	 * @param string|null $user_id
	 * @return array|Credential
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialByGUID(string $credential_guid, string $user_id = null) {
		$credential = $this->credentialMapper->getCredentialByGUID($credential_guid, $user_id);
		return $this->encryptService->decryptCredential($credential);
	}
}
