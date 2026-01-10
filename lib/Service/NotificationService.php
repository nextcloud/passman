<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Passman\Service;


use OCA\PassmanNext\AppInfo\Application;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Notification\IManager;

class NotificationService {
    public const APP_URL_PREFIX = 'index.php/apps/' . Application::APP_ID;

	public function __construct(
		private readonly IManager $manager,
		private readonly IURLGenerator $urlGenerator,
		private readonly IDBConnection $db,
	) {
	}

	public function credentialExpiredNotification($credential, $link) {
		$api = $this->urlGenerator->getAbsoluteURL($this->urlGenerator->linkTo('', self::APP_URL_PREFIX));
		$notification = $this->manager->createNotification();
		$remindAction = $notification->createAction();
		$remindAction->setLabel('remind')
			->setLink($api . '/api/internal/notifications/remind/' . $credential->getId(), 'POST');

		$declineAction = $notification->createAction();
		$declineAction->setLabel('ignore')
			->setLink($api . '/api/internal/notifications/read/' . $credential->getId(), 'DELETE');

		$notification->setApp(Application::APP_ID)
			->setUser($credential->getUserId())
			->setDateTime(new \DateTime())
			->setObject('credential', $credential->getId()) // Set notification type and id
			->setSubject('credential_expired', [$credential->getLabel()]) // set subject and parameters
			->setLink($link)
			->addAction($declineAction)
			->addAction($remindAction);

		$this->manager->notify($notification);
	}


	public function credentialSharedNotification($data) {
		$link = $this->urlGenerator->getAbsoluteURL($this->urlGenerator->linkTo('', self::APP_URL_PREFIX . '/#/'));
		$api = $this->urlGenerator->getAbsoluteURL($this->urlGenerator->linkTo('', self::APP_URL_PREFIX));
		$notification = $this->manager->createNotification();

		$declineAction = $notification->createAction();
		$declineAction->setLabel('decline')
			->setLink($api . '/api/v2/sharing/decline/' . $data['req_id'], 'DELETE');

		$notification->setApp(Application::APP_ID)
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_shared', [$data['from_user'], $data['credential_label']]) // subject and parameters
			->setLink($link)
			->addAction($declineAction);

		$this->manager->notify($notification);
	}


	public function credentialDeclinedSharedNotification($data) {
		$notification = $this->manager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_share_denied', [$data['from_user'], $data['credential_label']]); // subject and parameters
		$this->manager->notify($notification);
	}


	public function credentialAcceptedSharedNotification($data) {
		$notification = $this->manager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setUser($data['target_user'])
			->setDateTime(new \DateTime())
			->setObject('passman_share_request', $data['req_id']) // type and id
			->setSubject('credential_share_accepted', [$data['from_user'], $data['credential_label']]); // subject and parameters
		$this->manager->notify($notification);
	}

	public function hasCredentialExpirationNotification($credential): bool {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('notifications')
			->where($qb->expr()->eq('object_id', $qb->createNamedParameter($credential->getId(), IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('subject', $qb->createNamedParameter('credential_expired', IQueryBuilder::PARAM_STR)));
			return $qb->executeQuery()->rowCount() !== 0;
	}

	public function deleteNotificationsOfCredential($credential) {
		$qb = $this->db->getQueryBuilder();
		$qb->delete('notifications')
			->where($qb->expr()->eq('object_id', $qb->createNamedParameter($credential->getId(), IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('object_type', $qb->createNamedParameter('credential', IQueryBuilder::PARAM_STR)));
		return $qb->executeStatement();
	}

	public function markNotificationOfCredentialAsProcessed(int $credential_id, string $user_id): void {
		$notification = $this->manager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setObject('credential', $credential_id)
			->setUser($user_id);
		$this->manager->markProcessed($notification);
	}
}
