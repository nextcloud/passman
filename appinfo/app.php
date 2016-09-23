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

namespace OCA\Passman\AppInfo;


use OCP\Util;
use OCP\BackgroundJob;
use OCA\Passman\Notifier;

require_once __DIR__ . '/autoload.php';

$app = new \OCA\Passman\AppInfo\Application();
$app->registerNavigationEntry();
$app->registerPersonalPage();


$l = \OC::$server->getL10N('passman');
$manager = \OC::$server->getNotificationManager();
$manager->registerNotifier(function() {
	return new Notifier(
		\OC::$server->getL10NFactory()
	);
}, function() use ($l) {
	return [
		'id' => 'passman',
		'name' => $l->t('Passwords'),
	];
});

/**
 * Loading translations
 *
 * The string has to match the app's folder name
 */
Util::addTranslations('passman');

\OCP\BackgroundJob::addRegularTask('\OCA\Passman\Cron\ExpireCredentials', 'run');