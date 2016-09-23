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

namespace OCA\Passman\Service;

use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\FileMapper;


class NotificationService {

	private $manager;

	public function __construct(FileMapper $fileMapper) {
		$this->manager = \OC::$server->getNotificationManager();
	}

	function credentialExpiredNotification($credential){
		$notification = $this->manager->createNotification();
		$acceptAction = $notification->createAction();
		$acceptAction->setLabel('change')
					 ->setLink('/apps/passman/api/v1/', 'POST');

		$declineAction = $notification->createAction();
		$declineAction->setLabel('ignore')
			->setLink('/apps/passman/internal/notifications/read', 'DELETE');

		$notification->setApp('passman')
			->setUser($credential->getUserId())
			->setDateTime(new \DateTime())
			->setObject('credential', $credential->getId()) // $type and $id
			->setSubject('credential_expired', [$credential->getLabel()]) // $subject and $parameters
			->setLink('/apps/passman/#/vault/'. $credential->getVaultId() .'/edit/'. $credential->getId())
			->addAction($acceptAction)
			->addAction($declineAction);

		$this->manager->notify($notification);
	}

}