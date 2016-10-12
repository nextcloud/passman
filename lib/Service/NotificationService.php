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

	public function __construct() {
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
			->setObject('credential', $credential->getId()) // Set notification type and id
			->setSubject('credential_expired', [$credential->getLabel()]) // set subject and parameters
			->setLink($link)
			->addAction($declineAction)
			->addAction($remindAction);

		$this->manager->notify($notification);
	}


	function credentialSharedNotification($data){
		$urlGenerator = \OC::$server->getURLGenerator();
		$link = $urlGenerator->getAbsoluteURL($urlGenerator->linkTo('','index.php/apps/passman/#/'));
		$api = $urlGenerator->getAbsoluteURL($urlGenerator->linkTo('', 'index.php/apps/passman'));
		$notification = $this->manager->createNotification();

		$declineAction = $notification->createAction();
		$declineAction->setLabel('decline')
			->setLink($api . '/api/v2/sharing/decline/'. $data['req_id'], 'DELETE');

		$notification->setApp('passman')
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_shared', [$data['from_user'], $data['credential_label']]) // subject and parameters
			->setLink($link)
			->addAction($declineAction);

		$this->manager->notify($notification);
	}


	function credentialDeclinedSharedNotification($data){
		$notification = $this->manager->createNotification();
		$notification->setApp('passman')
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_share_denied', [$data['from_user'], $data['credential_label']]); // subject and parameters
		$this->manager->notify($notification);
	}


	function credentialAcceptedSharedNotification($data){
		$notification = $this->manager->createNotification();
		$notification->setApp('passman')
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_share_accepted', [$data['from_user'], $data['credential_label']]); // subject and parameters
		$this->manager->notify($notification);
	}

}