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


namespace OCA\Passman\Cron;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Utility\Utils;
use \OCA\Passman\AppInfo\Application;
class ExpireCredentials {
	public static function run() {
		$app = new Application();
		$container = $app->getContainer();
		$container->query('CronService')->expireCredentials();
	}
}