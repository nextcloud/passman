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

class CronService {

	private $credentialService;
	private $logger;
	private $utils;
	private $notificationService;

	public function __construct(CredentialService $credentialService, ILogger $logger, Utils $utils, NotificationService $notificationService) {
		$this->credentialService = $credentialService;
		$this->logger = $logger;
		$this->utils = $utils;
		$this->notificationService = $notificationService;
	}

	public function expireCredentials() {
		$this->logger->info('Passman cron test', array('app' => 'passman'));
		$expired_credentials = $this->credentialService->getExpiredCredentials($this->utils->getTime());
		foreach($expired_credentials as $credential){
			$this->notificationService->credentialExpiredNotification($credential);
		}
	}
}