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

use OCP\AppFramework\Http\JSONResponse;
use PHPUnit_Framework_TestCase;


/**
 * Class TranslationControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Controller\TranslationController
 */
class TranslationControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$IL10N = $this->getMockBuilder('OCP\IL10N')->getMock();

		$this->controller = new TranslationController(
			'passman', $request, $IL10N
		);
	}

	/**
	 * @covers ::getLanguageStrings
	 */
	public function testGetLanguageStrings() {
		$result = $this->controller->getLanguageStrings(null);
		$this->assertTrue($result instanceof JSONResponse);
	}
}
