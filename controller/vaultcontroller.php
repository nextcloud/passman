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

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Service\CredentialService;


class VaultController extends ApiController {
	private $userId;
	private $vaultService;
	private $credentialService;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								VaultService $vaultService,
								CredentialService $credentialService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->vaultService = $vaultService;
		$this->credentialService = $credentialService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function listVaults() {

		$vaults = $this->vaultService->getByUser($this->userId);
		return new JSONResponse($vaults);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create($vault_name) {
		$vault = $this->vaultService->createVault($vault_name, $this->userId);
		return new JSONResponse($vault);
	}

	/**
	 * @NoAdminRequired
	 */
	public function get($vault_id) {
		$credentials = $this->credentialService->getCredentialsByVaultId($vault_id, $this->userId);
		$vault = $this->vaultService->getById($vault_id, $this->userId);
		$vault = $vault[0];
		if($vault) {
			$result = array(
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
			$result['credentials'] = $credentials;
			$this->vaultService->setLastAccess($vault_id, $this->userId);
		} else {
			$result = array();
		}
		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update($vault_id) {
		$this->vaultService->getById($vault_id, $this->userId);

	}

	/**
	 * @NoAdminRequired
	 */
	public function updateSharingKeys($vault_id, $private_sharing_key, $public_sharing_key) {
		$this->vaultService->updateSharingKeys($vault_id, $private_sharing_key, $public_sharing_key);
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete($vault_id) {
		return;
	}
}