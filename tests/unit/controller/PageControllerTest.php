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

use OCP\AppFramework\Http\TemplateResponse;

/**
 * Class PageControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Controller\PageController
 */
class PageControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'passman', $request, $this->userId
		);
	}

	/**
	 * @covers ::index
	 */
	public function testIndex() {
		$result = $this->controller->index();
		$this->assertEquals(['user' => 'john'], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

	/**
	 * @covers ::bookmarklet
	 */
	public function testBookmarklet() {
		$result = $this->controller->bookmarklet('http://google.com', 'Google');
		$this->assertEquals(['url' => 'http://google.com', 'title' => 'Google'], $result->getParams());
		$this->assertEquals('bookmarklet', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

	/**
	 * @covers ::publicSharePage
	 */
	public function testPublicSharePage() {
		$result = $this->controller->publicSharePage();
		$this->assertEquals('public_share', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}