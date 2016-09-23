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
use OCP\ILogger;
use OCA\Passman\Utility\Utils;
use OCA\Passman\Activity;
use OCP\IDBConnection;
class CronService {

	private $credentialService;
	private $logger;
	private $utils;
	private $notificationService;
	private $activityService;
	private $db;
	public function __construct(CredentialService $credentialService, ILogger $logger, Utils $utils, NotificationService $notificationService, ActivityService $activityService, $db) {
		$this->credentialService = $credentialService;
		$this->logger = $logger;
		$this->utils = $utils;
		$this->notificationService = $notificationService;
		$this->activityService = $activityService;
		$this->db = $db;
	}

	public function expireCredentials() {
		$this->logger->info('Passman cron test', array('app' => 'passman'));
		$expired_credentials = $this->credentialService->getExpiredCredentials($this->utils->getTime());
		foreach($expired_credentials as $credential){
			$link = ''; // @TODO create direct link to credential

			$sql = 'SELECT count(*) as rows from `*PREFIX*notifications` WHERE `subject`= \'credential_expired\' AND object_id=?';
			$query = $this->db->prepareQuery($sql);
			$query->bindParam(1, $credential->getId(), \PDO::PARAM_INT);
			$result = $query->execute();
			if($result->fetchRow()['rows'] === 0) {
				$this->activityService->add(
					Activity::SUBJECT_ITEM_EXPIRED, array($credential->getLabel(), $credential->getUserId()),
					'', array(),
					$link, $credential->getUserId(), Activity::TYPE_ITEM_ACTION);
				$this->notificationService->credentialExpiredNotification($credential);
			}

		}
	}
}