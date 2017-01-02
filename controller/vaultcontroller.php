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

namespace OCA\Passman\Controller;

use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Service\CredentialService;


class VaultController extends ApiController {
	private $userId;
	private $vaultService;
	private $credentialService;
	private $settings;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								VaultService $vaultService,
								CredentialService $credentialService,
								SettingsService $settings,
								EncryptService $encryptService) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH',
			'Authorization, Content-Type, Accept',
			86400);
		$this->userId = $UserId;
		$this->vaultService = $vaultService;
		$this->credentialService = $credentialService;
		$this->settings = $settings;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function listVaults() {
		$result = array();
		$vaults = $this->vaultService->getByUser($this->userId);

		$protected_credential_fields = array('getDescription', 'getEmail', 'getUsername', 'getPassword');
		if ($vaults) {
			foreach ($vaults as $vault) {
				$credential = $this->credentialService->getRandomCredentialByVaultId($vault->getId(), $this->userId);
				$secret_field = $protected_credential_fields[array_rand($protected_credential_fields)];
				array_push($result, array(
					'vault_id' => $vault->getId(),
					'guid' => $vault->getGuid(),
					'name' => $vault->getName(),
					'created' => $vault->getCreated(),
					'public_sharing_key' => $vault->getPublicSharingKey(),
					'last_access' => $vault->getlastAccess(),
					'challenge_password' => $credential->{$secret_field}(),
				));
			}
		}

		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function create($vault_name) {
		$vault = $this->vaultService->createVault($vault_name, $this->userId);
		return new JSONResponse($vault);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get($vault_guid) {
		$vault = null;
		try {
			$vault = $this->vaultService->getByGuid($vault_guid, $this->userId);
		} catch (\Exception $e) {
			return new NotFoundJSONResponse();
		}
		$result = array();
		if ($vault) {
			$credentials = $this->credentialService->getCredentialsByVaultId($vault->getId(), $this->userId);

			$result = array(
				'vault_id' => $vault->getId(),
				'guid' => $vault->getGuid(),
				'name' => $vault->getName(),
				'created' => $vault->getCreated(),
				'private_sharing_key' => $vault->getPrivateSharingKey(),
				'public_sharing_key' => $vault->getPublicSharingKey(),
				'sharing_keys_generated' => $vault->getSharingKeysGenerated(),
				'vault_settings' => $vault->getVaultSettings(),
				'last_access' => $vault->getlastAccess()
			);
			$result['credentials'] = $credentials;

			$this->vaultService->setLastAccess($vault->getId(), $this->userId);
		}


		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function update($vault_guid, $name, $vault_settings) {
		$vault = $this->vaultService->getByGuid($vault_guid, $this->userId);
		if ($name && $vault) {
			$vault->setName($name);
		}
		if ($vault_settings && $vault) {
			$vault->setVaultSettings($vault_settings);
		}
		$this->vaultService->updateVault($vault);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function updateSharingKeys($vault_guid, $private_sharing_key, $public_sharing_key) {
		$vault = null;
		try {
			$vault = $this->vaultService->getByGuid($vault_guid, $this->userId);
		} catch (\Exception $e) {
			// No need to catch the execption
		}

		if ($vault) {
			$this->vaultService->updateSharingKeys($vault->getId(), $private_sharing_key, $public_sharing_key);
		}

		return;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function delete($vault_id) {
		return new JSONResponse($vault_id);
	}
}