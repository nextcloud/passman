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
use OC\Files\View;

use OCA\Passman\Controller\CredentialController;
use OCA\Passman\Controller\PageController;
use OCA\Passman\Controller\ShareController;
use OCA\Passman\Controller\VaultController;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CronService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Utility\Utils;
use OCA\Passman\Service\NotificationService;

use OCP\AppFramework\App;
use OCP\IL10N;
use OCP\Util;
class Application extends App {
	public function __construct () {
		parent::__construct('passman');
		$container = $this->getContainer();
		$server = $container->getServer();
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
				$server->getShareManager(),
				$server->getURLGenerator(),
				$server->getL10N($c->query('AppName'))
			);
		});



		/** Cron  **/
		$container->registerService('CronService', function ($c) {
			return new CronService(
				$c->query('CredentialService'),
				$c->query('Logger'),
				$c->query('Utils'),
				$c->query('NotificationService'),
				$c->query('ActivityService')
			);
		});


		$container->registerService('Logger', function($c) {
			return $c->query('ServerContainer')->getLogger();
		});

		// Aliases for the controllers so we can use the automatic DI
		$container->registerAlias('CredentialController', CredentialController::class);
		$container->registerAlias('PageController', PageController::class);
		$container->registerAlias('VaultController', VaultController::class);
		$container->registerAlias('CredentialService', CredentialService::class);
		$container->registerAlias('NotificationService', NotificationService::class);
		$container->registerAlias('ActivityService', ActivityService::class);
		$container->registerAlias('Utils', Utils::class);
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