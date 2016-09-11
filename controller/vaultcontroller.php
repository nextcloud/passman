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
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\VaultService;
class VaultController extends ApiController {
	private $userId;
	private $vaultService;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								VaultService $vaultService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->vaultService = $vaultService;
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
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function update($vault_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete($vault_id) {
		return;
	}
}