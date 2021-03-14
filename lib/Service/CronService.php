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

use OCA\Passman\Activity;
use OCA\Passman\Utility\Utils;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class CronService {

	private CredentialService $credentialService;
	private LoggerInterface $logger;
	private Utils $utils;
	private NotificationService $notificationService;
	private ActivityService $activityService;
	private IDBConnection $db;

	public function __construct(CredentialService $credentialService, LoggerInterface $logger, Utils $utils, NotificationService $notificationService, ActivityService $activityService, IDBConnection $db) {
		$this->credentialService = $credentialService;
		$this->logger = $logger;
		$this->utils = $utils;
		$this->notificationService = $notificationService;
		$this->activityService = $activityService;
		$this->db = $db;
	}

	public function expireCredentials() {
		$expired_credentials = $this->credentialService->getExpiredCredentials($this->utils->getTime());
		foreach ($expired_credentials as $credential) {
			$link = ''; // @TODO create direct link to credential
			$qb = $this->db->getQueryBuilder();
			$qb->select('*')
				->from('notifications')
				->where($qb->expr()->eq('object_id', $qb->createNamedParameter($credential->getId(), IQueryBuilder::PARAM_INT)))
				->andWhere($qb->expr()->eq('subject', $qb->createNamedParameter('credential_expired', IQueryBuilder::PARAM_STR)));

			try {
				$this->logger->debug($credential->getLabel() . ' is expired, checking notifications!', array('app' => 'passman'));
				$notificationCount = $qb->execute()->rowCount();
				if ($notificationCount === 0) {
					$this->logger->debug($credential->getLabel() . ' is expired, adding notification!', array('app' => 'passman'));
					$this->activityService->add(
						Activity::SUBJECT_ITEM_EXPIRED, array($credential->getLabel(), $credential->getUserId()),
						'', array(),
						$link, $credential->getUserId(), Activity::TYPE_ITEM_EXPIRED);
					$this->notificationService->credentialExpiredNotification($credential);
				}
			} catch (Exception $exception) {
				$this->logger->error('Error while creating a notification: ' . $exception->getMessage(), array('app' => 'passman'));
			}
		}
	}
}
