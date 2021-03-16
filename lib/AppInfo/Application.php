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
use OC\ServerContainer;
use OCA\Passman\Controller\ShareController;
use OCA\Passman\Middleware\APIMiddleware;
use OCA\Passman\Middleware\ShareMiddleware;
use OCA\Passman\Notifier;
use OCA\Passman\Search\Provider;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\CronService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\Utils;
use OCA\UserStatus\Listener\UserDeletedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\User\Events\BeforeUserDeletedEvent;
use OCP\Util;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Application extends App implements IBootstrap {
	public const APP_ID = 'passman';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$this->registerNavigationEntry();

		$context->registerEventListener(
			BeforeUserDeletedEvent::class,
			UserDeletedListener::class
		);

		$context->registerSearchProvider(Provider::class);

		$context->registerService(View::class, function () {
			return new View('');
		}, false);

		$context->registerService('isCLI', function () {
			return \OC::$CLI;
		});

		$context->registerMiddleware(ShareMiddleware::class);
		$context->registerMiddleware(APIMiddleware::class);

		$context->registerService('ShareController', function (ContainerInterface $c) {
			/** @var IUserManager $userManager */
			$userManager = $c->get(IUserManager::class);
			/** @var IGroupManager $groupManager */
			$groupManager = $c->get(IGroupManager::class);
			/** @var IUserSession $userSession */
			$userSession = $c->get(IUserSession::class);

			return new ShareController(
				$c->get('AppName'),
				$c->get('Request'),
				$userSession->getUser(),
				$groupManager,
				$userManager,
				$c->get(ActivityService::class),
				$c->get(VaultService::class),
				$c->get(ShareService::class),
				$c->get(CredentialService::class),
				$c->get(NotificationService::class),
				$c->get(FileService::class),
				$c->get(SettingsService::class),
				$c->get(IManager::class)
			);
		});


		$context->registerService('CronService', function (ContainerInterface $c) {
			return new CronService(
				$c->get(CredentialService::class),
				$c->get(LoggerInterface::class),
				$c->get(Utils::class),
				$c->get(NotificationService::class),
				$c->get(ActivityService::class),
				$c->get(IDBConnection::class)
			);
		});

		$context->registerService('Logger', function (ContainerInterface $c) {
			return $c->get(ServerContainer::class)->getLogger();
		});
	}

	public function boot(IBootContext $context): void {
		/** @var IManager $manager */
		$manager = $context->getAppContainer()->get(IManager::class);
		$manager->registerNotifierService(Notifier::class);

		Util::addTranslations(self::APP_ID);
	}

	/**
	 * Register the navigation entry
	 */
	public function registerNavigationEntry() {
		$c = $this->getContainer();
		/** @var INavigationManager $navigationManager */
		$navigationManager = $c->get(INavigationManager::class);

		$navigationEntry = function () use ($c) {
			/** @var IURLGenerator $urlGenerator */
			$urlGenerator = $c->get(IURLGenerator::class);
			return [
				'id' => $c->getAppName(),
				'order' => 10,
				'name' => $c->get(IL10N::class)->t('Passwords'),
				'href' => $urlGenerator->linkToRoute('passman.page.index'),
				'icon' => $urlGenerator->imagePath($c->getAppName(), 'app.svg'),
			];
		};
		$navigationManager->add($navigationEntry);
	}
}
