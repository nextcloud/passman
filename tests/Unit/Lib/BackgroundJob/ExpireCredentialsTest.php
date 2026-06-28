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

namespace OCA\Passman\Tests\Unit\Lib\BackgroundJob;

use OCA\Passman\BackgroundJob\ExpireCredentials;
use OCA\Passman\Service\CronService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Test\TestCase;

#[CoversClass(ExpireCredentials::class)]
class ExpireCredentialsTest extends TestCase {
	public function testRun(): void {
		$cronService = $this->createMock(CronService::class);
		$cronService->expects($this->once())->method('expireCredentials');

		$job = new ExpireCredentials(
			$this->createMock(ITimeFactory::class),
			$this->createMock(IConfig::class),
			$cronService,
		);

		$method = (new ReflectionClass($job))->getMethod('run');
		$method->invoke($job, null);
	}
}
