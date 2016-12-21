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

use OCA\Passman\Utility\Utils;

/**
 * @coversDefaultClass OCA\Passman\Utility\Utils
 */
class UtilsTest extends PHPUnit_Framework_TestCase {
	/**
	 * @covers ::GUID
	 */
	public function testGUID(){
		$pattern = '/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/';
		$this->assertTrue(preg_match($pattern, Utils::GUID()) === 1);
		$this->assertRegExp($pattern, Utils::GUID());
	}

	/**
	 * @covers ::GUID
	 */
	public function testGUIDLegacy() {
		$this->testGUID();
	}

	/**
	 * @covers ::getTime
	 */
	public function testGetTime(){
		// Check that the time is 1s bigger upon two successive calls to getTime
		$old_time = Utils::getTime();
		sleep(1);
		$new_time = Utils::getTime();

		$this->assertEquals($old_time +1, $new_time, "Evaluating that $old_time +1 === $new_time");
	}

	/**
	 * @covers ::getMicroTime
	 */
	public function testGetMicroTime(){
		$old_time = Utils::getMicroTime();
		usleep(10);
		$new_time = Utils::getMicroTime();

		$this->assertTrue($old_time < $new_time, "Evaluating that $old_time < $new_time");
	}
}