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

use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\CredentialService;
use \OCP\App;

class InternalController extends ApiController {
	private $userId;
	private $credentialService;
	private $config;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								CredentialService $credentialService,
								IConfig $config
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
		$this->config = $config;
	}

	/**
	 * @NoAdminRequired
	 */
	public function remind($credential_id) {
		$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		$credential->setExpireTime(time() + (24 * 60 * 60));
		$this->credentialService->upd($credential);

		$manager = \OC::$server->getNotificationManager();
		$notification = $manager->createNotification();
		$notification->setApp('passman')
			->setObject('credential', $credential_id)
			->setUser($this->userId);
		$manager->markProcessed($notification);
	}

	/**
	 * @NoAdminRequired
	 */
	public function read($credential_id) {

		$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		$credential->setExpireTime(0);
		$this->credentialService->upd($credential);

		$manager = \OC::$server->getNotificationManager();
		$notification = $manager->createNotification();
		$notification->setApp('passman')
			->setObject('credential', $credential_id)
			->setUser($this->userId);
		$manager->markProcessed($notification);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getAppVersion() {
		$AppInstance = new App();
		return new JSONResponse(array('version' => $AppInstance->getAppInfo("passman")["version"]));
	}

	/**
	 * @NoAdminRequired
	 */
	public function generatePerson() {
		$random_person = json_decode(file_get_contents('http://api.namefake.com/'));
		return new JSONResponse($random_person);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getSettings() {
		$settings = array(
			'link_sharing_enabled' => $this->config->getAppValue('passman', 'link_sharing_enabled', 1),
			'user_sharing_enabled' => $this->config->getAppValue('passman', 'user_sharing_enabled', 1),
			'vault_key_strength' => $this->config->getAppValue('passman', 'vault_key_strength', 3),
			'check_version' => $this->config->getAppValue('passman', 'check_version', 1),
			'https_check' => $this->config->getAppValue('passman', 'https_check', 1),
			'disable_contextmenu' => $this->config->getAppValue('passman', 'disable_contextmenu', 1),
		);
		return new JSONResponse($settings);
	}

	/**
	 * @NoCSRFRequired
	 */
	public function saveSettings($key, $value) {
		if (is_numeric($value)) {
			$value = intval($value);
		}
		$this->config->setAppValue('passman', $key, $value);
	}

}