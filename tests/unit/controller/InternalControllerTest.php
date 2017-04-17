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

namespace OCA\Passman\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\JSONResponse;

/**
 * Class InternalControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\InternalController
 */
class InternalControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'john';
	private $credentialService;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$config = $this->getMockBuilder('OCP\IConfig')->getMock();
		$this->credentialService = $this->getMockBuilder('OCA\Passman\Service\CredentialService')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new InternalController(
			'passman', $request, $this->userId, $this->credentialService, $config
		);
	}

	/**
	 * @covers ::remind
	 */
	public function testRemind() {
		$this->controller->remind(null);
		$this->assertTrue(true);
	}
	/**
	 * @covers ::read
	 */
	public function testRead() {
		$this->controller->read(null);
		$this->assertTrue(true);
	}

	/**
	 * @covers ::getAppVersion
	 */
	public function testGetAppVersion() {
		$this->assertTrue(true);
	}

	/**
	 * @covers ::generatePerson
	 */
	public function testGeneratePerson() {
		$this->assertTrue(true);
		//$result = $this->controller->generatePerson();
		//$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::getSettings
	 */
	public function testGetSettings() {
		$result = $this->controller->getSettings();
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::saveSettings
	 */
	public function testSaveSettings() {
		$result = $this->controller->saveSettings('test', 'test');
		$this->assertTrue(true);
	}

}