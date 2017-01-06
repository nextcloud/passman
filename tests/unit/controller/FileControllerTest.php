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

use OCA\Comments\Activity\Setting;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Service\VaultService;
use OCP\IGroupManager;
use OCP\IUserManager;
use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\JSONResponse;

/**
 * Class FileControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\FileController
 */
class FileControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'example';
	private $credentialService;
	private $vaultService;
	private $groupManager;
	private $userManager;
	private $activityService;
	private $shareService;
	private $notificationService;
	private $fileService;
	private $settings;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->fileService = $this->createMock(FileService::class);
		$this->controller = new FileController(
			'passman', $request, $this->userId, $this->fileService
		);
	}

	/**
	 * @covers ::uploadFile
	 */
	public function testUploadFile() {
		$result = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::getFile
	 */
	public function testGetFile() {
		$result = $this->controller->getFile(null);
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::deleteFile
	 */
	public function testDeleteFile() {
		$result = $this->controller->deleteFile(null);
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::updateFile
	 */
	public function testUpdateFile() {
		$this->controller->updateFile('6AD30804-BFFC-4EFC-97F8-20A126FA1709', '0' , '0.jpg');
		$this->assertTrue(true);
	}
}