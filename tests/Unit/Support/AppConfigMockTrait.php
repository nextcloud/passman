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

namespace OCA\Passman\Tests\Unit\Support;

use OC\AppConfig;
use OCP\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

trait AppConfigMockTrait {
	/**
	 * @param callable(string, string, mixed): mixed|null $getValueCallback
	 */
	protected function createAppConfigMock(?callable $getValueCallback = null): IAppConfig&MockObject {
		$appConfig = $this->getMockBuilder(AppConfig::class)
			->disableOriginalConstructor()
			->onlyMethods(['getValue', 'setValue'])
			->getMock();

		if ($getValueCallback !== null) {
			$appConfig->method('getValue')->willReturnCallback($getValueCallback);
		} else {
			$appConfig->method('getValue')->willReturnArgument(2);
		}

		$appConfig->method('setValue')->willReturn(null);

		return $appConfig;
	}
}
