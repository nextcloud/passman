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

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\NotificationService;
use OCP\App\IAppManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class InternalController extends ApiController {
	public function __construct(
		$AppName,
		IRequest $request,
		private $userId,
		private readonly CredentialService $credentialService,
		private readonly NotificationService $notificationService,
		private readonly IConfig $config,
		private readonly IAppManager $appManager,
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
	}

	#[NoAdminRequired]
	public function remind($credential_id) {
		$this->setExpireTimeOfOwnCredential((int) $credential_id, time() + (24 * 60 * 60));
	}

	#[NoAdminRequired]
	public function read($credential_id) {
		$this->setExpireTimeOfOwnCredential((int) $credential_id, 0);
	}

	/**
	 * Expiry notifications (and their remind/read actions) are only sent to the credential owner,
	 * so only the owner may change the expire time. Users the credential is shared with,
	 * even with write permission, must not modify it through these endpoints.
	 *
	 * A missing and a foreign credential are handled identically, so the response does not reveal
	 * whether a credential id exists: nothing is updated, only the requesting user's own
	 * notification for that id (if any, e.g. for a credential deleted meanwhile) is dismissed.
	 */
	private function setExpireTimeOfOwnCredential(int $credential_id, int $expire_time): void {
		try {
			$credential = $this->credentialService->getOwnedCredentialById($credential_id, $this->userId);
		} catch (DoesNotExistException|MultipleObjectsReturnedException) {
			$this->notificationService->markNotificationOfCredentialAsProcessed($credential_id, $this->userId);
			return;
		}

		$credential->setExpireTime($expire_time);
		$this->credentialService->updateCredentialEntity($credential);

		$this->notificationService->markNotificationOfCredentialAsProcessed($credential_id, $this->userId);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function getAppVersion() {
		return new JSONResponse(['version' => $this->appManager->getAppInfo(Application::APP_ID)["version"]]);
	}

}
