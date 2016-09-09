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
use OCA\Passman\Credential;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

class CredentialController extends ApiController {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function createCredential() {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getCredential($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function updateCredential($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteCredential($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getRevision($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteRevision($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function uploadFile($credential_id) {
		return;
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteFile($credential_id) {
		return;
	}
}