<?php
/**
 * Nextcloud - passman
 *
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

use OCP\AppFramework\Http\Attribute\AppApiAdminAccessWithoutUser;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\ExAppRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\SubAdminRequired;
use PHPUnit\Framework\Attributes\CoversNothing;
use Test\TestCase;

#[CoversNothing]
class RoutesTest extends TestCase {
	/**
	 * Routes that must stay admin-only. Nextcloud's SecurityMiddleware requires an admin
	 * for every controller method unless an attribute (or annotation) relaxes it.
	 */
	private const ADMIN_ONLY_ROUTES = [
		'Admin#searchUser',
		'Admin#moveCredentials',
		'Admin#listRequests',
		'Admin#acceptRequestDeletion',
		'Settings#saveAdminSetting',
	];

	/**
	 * Admin controller routes that are deliberately callable by any logged-in user.
	 * These do their own authorization checks.
	 */
	private const ADMIN_CONTROLLER_USER_ROUTES = [
		'Admin#requestDeletion',
		'Admin#deleteRequestDeletion',
	];

	/**
	 * Attributes that lift the admin requirement of the SecurityMiddleware.
	 */
	private const ADMIN_REQUIREMENT_RELAXING_ATTRIBUTES = [
		NoAdminRequired::class,
		PublicPage::class,
		SubAdminRequired::class,
		AuthorizedAdminSetting::class,
		ExAppRequired::class,
		AppApiAdminAccessWithoutUser::class,
	];

	/**
	 * @return list<array{name: string, url: string, verb: string}>
	 */
	private static function routes(): array {
		$routes = require __DIR__ . '/../../../appinfo/routes.php';
		return $routes['routes'];
	}

	/**
	 * Resolve a route name like 'Vault#preflighted_cors' the way Nextcloud does:
	 * controller and action are converted from snake_case to CamelCase / camelCase.
	 *
	 * @return array{0: class-string, 1: string}
	 */
	private static function resolve(string $name): array {
		[$controller, $action] = explode('#', $name, 2);
		$class = 'OCA\\Passman\\Controller\\' . str_replace('_', '', ucwords($controller, '_')) . 'Controller';
		$method = lcfirst(str_replace('_', '', ucwords($action, '_')));
		return [$class, $method];
	}

	public function testEveryRouteTargetsAnExistingPublicControllerMethod(): void {
		foreach (self::routes() as $route) {
			[$class, $method] = self::resolve($route['name']);

			$this->assertTrue(class_exists($class), "Route {$route['name']}: controller $class does not exist");
			$this->assertTrue(method_exists($class, $method), "Route {$route['name']}: method $class::$method does not exist");
			$this->assertTrue(
				(new \ReflectionMethod($class, $method))->isPublic(),
				"Route {$route['name']}: method $class::$method is not public"
			);
		}
	}

	public function testAdminOnlyRoutesDoNotRelaxTheAdminRequirement(): void {
		$routeNames = array_column(self::routes(), 'name');

		foreach (self::ADMIN_ONLY_ROUTES as $name) {
			$this->assertContains($name, $routeNames, "Admin-only route $name is not routed anymore, update the list");

			[$class, $method] = self::resolve($name);
			$reflection = new \ReflectionMethod($class, $method);

			foreach (self::ADMIN_REQUIREMENT_RELAXING_ATTRIBUTES as $attribute) {
				$this->assertEmpty(
					$reflection->getAttributes($attribute),
					"Route $name: $class::$method must stay admin-only but has #[$attribute]"
				);
			}

			// Nextcloud still honours the deprecated docblock annotations
			$this->assertDoesNotMatchRegularExpression(
				'/@(NoAdminRequired|PublicPage|SubAdminRequired|AuthorizedAdminSetting|ExAppRequired)\b/',
				(string) $reflection->getDocComment(),
				"Route $name: $class::$method must stay admin-only but has an annotation relaxing it"
			);
		}
	}

	public function testEveryAdminControllerRouteIsClassified(): void {
		// A new route on the AdminController has to be consciously added to one of the lists
		foreach (self::routes() as $route) {
			if (str_starts_with($route['name'], 'Admin#')) {
				$this->assertContains(
					$route['name'],
					[...self::ADMIN_ONLY_ROUTES, ...self::ADMIN_CONTROLLER_USER_ROUTES],
					"Route {$route['name']} is neither listed as admin-only nor as user route"
				);
			}
		}
	}
}
