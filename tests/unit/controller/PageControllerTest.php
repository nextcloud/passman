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


class PageControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'passman', $request, $this->userId
		);
	}

	public function testIndex() {
		$result = $this->controller->index();
		$this->assertEquals(['user' => 'john'], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

	public function testBookmarklet() {
		$result = $this->controller->bookmarklet('http://google.com', 'Google');
		print_r($result->getParams());
		$this->assertEquals(['url' => 'http://google.com', 'title' => 'Google'], $result->getParams());
		$this->assertEquals('bookmarklet', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

	public function testPublicSharePage() {
		$result = $this->controller->publicSharePage();
		$this->assertEquals('public_share', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}