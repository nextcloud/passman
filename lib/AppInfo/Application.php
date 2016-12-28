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

namespace OCA\Passman\AppInfo;
use OC\Files\View;

use OCA\Passman\Controller\CredentialController;
use OCA\Passman\Controller\PageController;
use OCA\Passman\Controller\ShareController;
use OCA\Passman\Controller\VaultController;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CronService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\Utils;
use OCA\Passman\Service\NotificationService;
use OCP\IConfig;
use OCP\IDBConnection;

use OCP\AppFramework\App;
use OCP\IL10N;
use OCP\Util;
class Application extends App {
	public function __construct () {
		parent::__construct('passman');
		$container = $this->getContainer();
		// Allow automatic DI for the View, until we migrated to Nodes API
		$container->registerService(View::class, function() {
			return new View('');
		}, false);
		$container->registerService('isCLI', function() {
			return \OC::$CLI;
		});

		/**
		 * Controllers
		 */
		$container->registerService('ShareController', function($c) {
			$container = $this->getContainer();
			$server = $container->getServer();
			return new ShareController(
				$c->query('AppName'),
				$c->query('Request'),
				$server->getUserSession()->getUser(),
				$server->getGroupManager(),
				$server->getUserManager(),
 				$c->query('ActivityService'),
 				$c->query('VaultService'),
                $c->query('ShareService'),
                $c->query('CredentialService'),
                $c->query('NotificationService'),
                $c->query('FileService'),
                $c->query('IConfig')
			);
		});



		/** Cron **/
		$container->registerService('CronService', function ($c) {
			return new CronService(
				$c->query('CredentialService'),
				$c->query('Logger'),
				$c->query('Utils'),
				$c->query('NotificationService'),
				$c->query('ActivityService'),
				$c->query('IDBConnection')
			);
		});

		$container->registerService('Db', function () {
			return new Db();
		});

		$container->registerService('Logger', function($c) {
			return $c->query('ServerContainer')->getLogger();
		});

		// Aliases for the controllers so we can use the automatic DI
		$container->registerAlias('CredentialController', CredentialController::class);
		$container->registerAlias('PageController', PageController::class);
		$container->registerAlias('VaultController', VaultController::class);
		$container->registerAlias('VaultController', VaultController::class);
		$container->registerAlias('CredentialService', CredentialService::class);
		$container->registerAlias('NotificationService', NotificationService::class);
		$container->registerAlias('ActivityService', ActivityService::class);
		$container->registerAlias('VaultService', VaultService::class);
		$container->registerAlias('FileService', FileService::class);
        $container->registerAlias('ShareService', ShareService::class);
		$container->registerAlias('Utils', Utils::class);
		$container->registerAlias('IDBConnection', IDBConnection::class);
		$container->registerAlias('IConfig', IConfig::class);
	}

	/**
	 * Register the navigation entry
	 */
	public function registerNavigationEntry() {
		$c = $this->getContainer();
		/** @var \OCP\IServerContainer $server */
		$server = $c->getServer();
		$navigationEntry = function () use ($c, $server) {
			return [
				'id' => $c->getAppName(),
				'order' => 10,
				'name' => $c->query(IL10N::class)->t('Passwords'),
				'href' => $server->getURLGenerator()->linkToRoute('passman.page.index'),
				'icon' => $server->getURLGenerator()->imagePath($c->getAppName(), 'app.svg'),
			];
		};
		$server->getNavigationManager()->add($navigationEntry);
	}

	/**
	 * Register personal settings for notifications and emails
	 */
	public function registerPersonalPage() {
		\OCP\App::registerPersonal($this->getContainer()->getAppName(), 'personal');
	}
}