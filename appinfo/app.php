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
use OCP\App;
use OCA\Passman\Notifier;
use OCA\Passman\Activity;
require_once __DIR__ . '/autoload.php';

$app = new \OCA\Passman\AppInfo\Application();
$app->registerNavigationEntry();
$app->registerPersonalPage();

$manager = \OC::$server->getNotificationManager();
$manager->registerNotifierService(Notifier::class);

/**
 * Loading translations
 *
 * The string has to match the app's folder name
 */
Util::addTranslations('passman');
\OCP\App::registerAdmin('passman', 'templates/admin.settings');
