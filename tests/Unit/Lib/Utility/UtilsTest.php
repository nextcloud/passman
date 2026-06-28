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

namespace OCA\Passman\Tests\Unit\Lib\Utility;

use OCA\Passman\Utility\Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[CoversClass(Utils::class)]
class UtilsTest extends TestCase {
	public function testGUID(): void {
		$pattern = '/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/';
		$this->assertSame(1, preg_match($pattern, Utils::GUID()));
		$this->assertMatchesRegularExpression($pattern, Utils::GUID());
	}

	#[Group('slow')]
	public function testGetTime(): void {
		$oldTime = Utils::getTime();
		sleep(1);
		$newTime = Utils::getTime();

		$this->assertEquals($oldTime + 1, $newTime, "Evaluating that $oldTime +1 === $newTime");
	}

	public function testGetMicroTime(): void {
		$oldTime = Utils::getMicroTime();
		usleep(10);
		$newTime = Utils::getMicroTime();

		$this->assertTrue($oldTime < $newTime, "Evaluating that $oldTime < $newTime");
	}
}
