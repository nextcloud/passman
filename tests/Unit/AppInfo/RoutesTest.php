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

use PHPUnit\Framework\Attributes\CoversNothing;
use Test\TestCase;

#[CoversNothing]
class RoutesTest extends TestCase {
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
}
