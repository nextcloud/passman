<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

declare(strict_types=1);

namespace OCA\Passman\Tests\Unit\AppInfo;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Controller\ShareController;
use OCP\App\IAppManager;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[CoversNothing]
#[Group(name: 'DB')]
class ApplicationTest extends TestCase {
	public function testAppInstalled(): void {
		$appManager = Server::get(IAppManager::class);
		$this->assertTrue($appManager->isInstalled(Application::APP_ID));
	}

	/**
	 * Routes like 'share#get_revisions' are resolved by the short controller name ('ShareController') first,
	 * which hits the manual registration in Application::register(). That registration injects the current
	 * IUser object, while autowired controllers get the 'userId' service (uid string) for a $userId parameter.
	 */
	public function testShareControllerGetsCurrentUserObjectInjected(): void {
		$userSession = Server::get(IUserSession::class);
		if (!method_exists($userSession, 'setVolatileActiveUser')) {
			$this->markTestSkipped('User session does not support volatile users');
		}

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('passman_di_test');

		$userSession->setVolatileActiveUser($user);
		try {
			$controller = Server::get(Application::class)->getContainer()->get('ShareController');
		} finally {
			$userSession->setVolatileActiveUser(null);
		}

		$this->assertInstanceOf(ShareController::class, $controller);
		$this->assertSame($user, (new \ReflectionProperty(ShareController::class, 'userId'))->getValue($controller));
	}
}
