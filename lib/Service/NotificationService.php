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
		$urlGenerator = \OC::$server->getURLGenerator();
		$link = $urlGenerator->getAbsoluteURL($urlGenerator->linkTo('','index.php/apps/passman/#/vault/'. $credential->getVaultId() .'/edit/'. $credential->getId()));
		$api = $urlGenerator->getAbsoluteURL($urlGenerator->linkTo('', 'index.php/apps/passman'));
		$notification = $this->manager->createNotification();
		$remindAction = $notification->createAction();
		$remindAction->setLabel('remind')
			->setLink($api. '/api/internal/notifications/remind/'. $credential->getId() , 'POST');

		$declineAction = $notification->createAction();
		$declineAction->setLabel('ignore')
			->setLink($api . '/api/internal/notifications/read/'. $credential->getId(), 'DELETE');

		$notification->setApp('passman')
			->setUser($credential->getUserId())
			->setDateTime(new \DateTime())
			->setObject('credential', $credential->getId()) // $type and $id
			->setSubject('credential_expired', [$credential->getLabel()]) // $subject and $parameters
			->setLink($link)
			->addAction($declineAction)
			->addAction($remindAction);

		$this->manager->notify($notification);
	}

}