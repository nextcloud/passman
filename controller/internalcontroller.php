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
use \OC_App;

class InternalController extends ApiController {
	private $userId;
	private $credentialService;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								CredentialService $credentialService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
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
	 */
	public function getAppVersion() {
		return new JSONResponse(array('version' => OC_App::getAppVersion('passman')));
	}

}