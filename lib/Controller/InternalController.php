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

use OCA\Passman\Service\CredentialService;
use OCP\App\IAppManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Notification\IManager;

class InternalController extends ApiController {
	private $userId;
	private $credentialService;
	private $config;
	private $manager;
	private $appManager;

	public function __construct($AppName,
	                            IRequest $request,
	                            $UserId,
	                            CredentialService $credentialService,
	                            IConfig $config,
	                            IManager $IManager,
	                            IAppManager $appManager
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
		$this->config = $config;
		$this->manager = $IManager;
		$this->appManager = $appManager;
	}

	/**
	 * @NoAdminRequired
	 */
	public function remind($credential_id) {
		$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		if ($credential) {
			$credential->setExpireTime(time() + (24 * 60 * 60));
			$this->credentialService->upd($credential);

			$notification = $this->manager->createNotification();
			$notification->setApp('passman')
				->setObject('credential', $credential_id)
				->setUser($this->userId);
			$this->manager->markProcessed($notification);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function read($credential_id) {
		$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		if ($credential) {
			$credential->setExpireTime(0);
			$this->credentialService->upd($credential);

			$notification = $this->manager->createNotification();
			$notification->setApp('passman')
				->setObject('credential', $credential_id)
				->setUser($this->userId);
			$this->manager->markProcessed($notification);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getAppVersion() {
		return new JSONResponse(array('version' => $this->appManager->getAppInfo('passman')["version"]));
	}

	/**
	 * @NoAdminRequired
	 */
	public function generatePerson() {
		$context = ['http' => ['method' => 'GET'], 'ssl' => ['verify_peer' => false, 'allow_self_signed' => true]];
		$context = stream_context_create($context);
		$random_person = json_decode(file_get_contents('http://api.namefake.com/', false, $context));
		return new JSONResponse($random_person);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getSettings() {
		$settings = array(
			'link_sharing_enabled' => intval($this->config->getAppValue('passman', 'link_sharing_enabled', 1)),
			'user_sharing_enabled' => intval($this->config->getAppValue('passman', 'user_sharing_enabled', 1)),
			'vault_key_strength' => intval($this->config->getAppValue('passman', 'vault_key_strength', 3)),
			'check_version' => intval($this->config->getAppValue('passman', 'check_version', 1)),
			'https_check' => intval($this->config->getAppValue('passman', 'https_check', 1)),
			'disable_contextmenu' => intval($this->config->getAppValue('passman', 'disable_contextmenu', 1)),
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
