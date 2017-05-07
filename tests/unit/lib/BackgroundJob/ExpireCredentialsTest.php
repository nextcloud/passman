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

namespace OCA\Passman\Tests\BackgroundJob;

use PHPUnit_Framework_TestCase;
use OCA\Passman\BackgroundJob\ExpireCredentials;
use OCP\IConfig;

/**
 * Class ExpireCredentialsTest
 *
 * @group DB
 * @package OCA\Passman\Tests\BackgroundJob
 * @covers \OCA\Passman\BackgroundJob\ExpireCredentials
 */
class ExpireCredentialsTest extends PHPUnit_Framework_TestCase {
	public function testRun() {
		$backgroundJob = new ExpireCredentials(
			$this->getMockBuilder(IConfig::class)->getMock()
		);

		$jobList = $this->getMockBuilder('\OCP\BackgroundJob\IJobList')->getMock();

		/** @var \OC\BackgroundJob\JobList $jobList */
		try {
		    $backgroundJob->execute($jobList);
		}
		catch (Exception $ex) {
		    $this->assertTrue(false);
		}
	}
}
