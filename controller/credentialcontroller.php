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
use OCA\Passman\Service\CredentialService;


class CredentialController extends ApiController {
	private $userId;
	private $credentialService;
	public function __construct($AppName,
								IRequest $request,
								$UserId,
								CredentialService $credentialService){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function createCredential($changed, $created,
									 $credential_id, $custom_fields, $delete_time,
									 $description, $email, $expire_time, $favicon, $files, $guid,
									 $hidden, $label, $otp, $password, $renew_interval,
									 $tags, $url, $username, $vault_id) {
		$credential = array(
			'credential_id' => $credential_id,
			'guid' => $guid,
			'user_id' => $this->userId,
			'vault_id' => $vault_id,
			'label' => $label,
			'description' => $description,
			'created' => $created,
			'changed' => $changed,
			'tags' => $tags,
			'email' => $email,
			'username' => $username,
			'password' => $password,
			'url' => $url,
			'favicon' => $favicon,
			'renew_interval' => $renew_interval,
			'expire_time' => $expire_time,
			'delete_time' => $delete_time,
			'files' => $files,
			'custom_fields' => $custom_fields,
			'otp' => $otp,
			'hidden' => $hidden,

		);
		$credential = $this->credentialService->createCredential($credential);
		return new JSONResponse($credential);
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
}