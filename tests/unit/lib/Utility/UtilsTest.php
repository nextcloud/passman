<?php

/**
 * Test case for the Utils class
 * Date: 8/10/16
 * Time: 17:47
 * @copyright Marcos Zuriaga Miguel 2016
 * @license AGPLv3
 */
use OCA\Passman\Utility\Utils;

/**
 * @coversDefaultClass OCA\Passman\Utility\Utils
 */
class UtilsTest extends PHPUnit_Framework_TestCase {
	/**
	 * @covers ::GUID
	 * @requires function com_create_guid
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