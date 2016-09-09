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
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

class VaultController extends ApiController {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function create() {
		return;
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