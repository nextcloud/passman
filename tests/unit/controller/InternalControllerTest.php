<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
 */

namespace OCA\Passman\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\TemplateResponse;


class InternalControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'passman', $request, $this->userId
		);
	}
	
	public function testGetAppVersion() {
		$result = $this->controller->generatePerson();
		$this->assertTrue($result instanceof JSONResponse);
	}
	public function testGeneratePerson() {
		$result = $this->controller->generatePerson();
		$this->assertTrue($result instanceof JSONResponse);
	}
}