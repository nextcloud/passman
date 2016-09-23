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

class CronService {

	private $credentialService;
	private $logger;
	private $utils;
	private $notificationService;
	private $activityService;

	public function __construct(CredentialService $credentialService, ILogger $logger, Utils $utils, NotificationService $notificationService, ActivityService $activityService) {
		$this->credentialService = $credentialService;
		$this->logger = $logger;
		$this->utils = $utils;
		$this->notificationService = $notificationService;
		$this->activityService = $activityService;
	}

	public function expireCredentials() {
		$this->logger->info('Passman cron test', array('app' => 'passman'));
		$expired_credentials = $this->credentialService->getExpiredCredentials($this->utils->getTime());
		foreach($expired_credentials as $credential){
			$this->notificationService->credentialExpiredNotification($credential);
			$link = ''; // @TODO create direct link to credential
			$this->activityService->add(
				Activity::SUBJECT_ITEM_EXPIRED, array($credential->getLabel(), $credential->getUserId()),
				'', array(),
				$link,  $credential->getUserId(), Activity::TYPE_ITEM_ACTION);

		}
	}
}