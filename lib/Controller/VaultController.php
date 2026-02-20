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

use OCA\Passman\Db\Credential;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\DeleteVaultRequestService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;


class VaultController extends ApiController {
	public function __construct(
		$AppName,
		IRequest $request,
		private $userId,
		private readonly VaultService $vaultService,
		private readonly CredentialService $credentialService,
		private readonly DeleteVaultRequestService $deleteVaultRequestService,
		private readonly SettingsService $settings,
		private readonly FileService $fileService,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function listVaults() {
		$result = [];
		$vaults = $this->vaultService->getByUser($this->userId);

		$protected_credential_fields = ['getDescription', 'getEmail', 'getUsername', 'getPassword'];
		if (isset($vaults)) {
			foreach ($vaults as $vault) {
				$credential = $this->credentialService->getRandomCredentialByVaultId($vault->getId(), $this->userId);
				$secret_field = $protected_credential_fields[array_rand($protected_credential_fields)];
				if (isset($credential)) {
					$result[] = [
						'vault_id' => $vault->getId(),
						'guid' => $vault->getGuid(),
						'name' => $vault->getName(),
						'created' => $vault->getCreated(),
						'public_sharing_key' => $vault->getPublicSharingKey(),
						'last_access' => $vault->getlastAccess(),
						'challenge_password' => $credential->{$secret_field}(),
						'delete_request_pending' => ($this->deleteVaultRequestService->getDeleteRequestForVault($vault->getGuid())) ? true : false
					];
				}
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
		} catch (\Exception) {
			return new NotFoundJSONResponse();
		}
		$result = [];
		if (isset($vault)) {
			$credentials = $this->credentialService->getCredentialsByVaultId($vault->getId(), $this->userId);

			$result = [
				'vault_id' => $vault->getId(),
				'guid' => $vault->getGuid(),
				'name' => $vault->getName(),
				'created' => $vault->getCreated(),
				'private_sharing_key' => $vault->getPrivateSharingKey(),
				'public_sharing_key' => $vault->getPublicSharingKey(),
				'sharing_keys_generated' => $vault->getSharingKeysGenerated(),
				'vault_settings' => $vault->getVaultSettings(),
				'last_access' => $vault->getlastAccess(),
				'delete_request_pending' => ($this->deleteVaultRequestService->getDeleteRequestForVault($vault->getGuid())) ? true : false
			];
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
		} catch (\Exception) {
			// No need to catch the exception
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
	public function delete($vault_guid) {
		$failed_credential_guids = [];
		try {
			$vault = $this->vaultService->getByGuid($vault_guid, $this->userId);
			$credentials = $this->credentialService->getCredentialsByVaultId($vault->getId(), $this->userId);

			foreach ($credentials as $credential) {
				if ($credential instanceof Credential) {
					try {
						// $credential = $this->credentialService->getCredentialByGUID($credential_guid, $this->userId);
						$this->credentialService->deleteCredentiaL($credential);
						$this->credentialService->deleteCredentialParts($credential, $this->userId);
					} catch (\Exception $e) {
						$this->logger->error('Error deleting credential (' . $credential->getId() . ') in vaultcontroller:delete()',
							['exception' => $e->getTrace(), 'message' => $e->getMessage()]);
						$failed_credential_guids[] = $credential->getGuid();
						continue;
					}
				}
			}
		} catch (\Exception) {
			return new NotFoundJSONResponse();
		}

		$this->vaultService->deleteVault($vault_guid, $this->userId);
		return new JSONResponse(['ok' => empty($failed_credential_guids), 'failed' => $failed_credential_guids]);
	}
}
