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

namespace OCA\Passman\Tests\Unit\Controller;

use OCA\Passman\Controller\FileController;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Service\FileService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class FileControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\FileController
 */
class FileControllerTest extends TestCase
{

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
	private $fileMapper;
	private $logger;
	private $settings;

	public function setUp(): void
    {
		$request = $this->getMockBuilder(IRequest::class)->getMock();
		$this->fileService = $this->getMockBuilder(FileService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileMapper = $this->getMockBuilder(FileMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
		$this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$this->controller = new FileController('passman', $request, $this->userId, $this->fileService, $this->logger);
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
    public function testGetExistingFile() {
        $uploadResult = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
        $this->assertTrue($uploadResult instanceof JSONResponse);

        var_dump($uploadResult->getData());
        var_dump($this->fileMapper->getFileGuidsFromUser($this->userId));

        $result = $this->controller->getFile(0);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertNull($result->getData());
    }

    /**
     * @covers ::getFile
     */
    public function testGetUnspecifiedFile() {
        try {
            $this->controller->getFile(null);
            $this->fail('Getting an unspecified file should ever fail');
        } catch (\TypeError $exception) {
            $this->assertStringContainsString('type int', $exception->getMessage());
        }
    }

    /**
     * @covers ::getFile
     */
    public function testGetUnknownFile() {
        $result = $this->controller->getFile(100000);
        $this->assertNull($result->getData());
    }

    /**
     * @covers ::deleteFile
     */
    /*public function testDeleteFile() {
        $result = $this->controller->deleteFile(null);
        $this->assertTrue($result instanceof JSONResponse);
    }*/

    /**
     * @covers ::updateFile
     */
	/*public function testUpdateFile() {
		$this->controller->updateFile('6AD30804-BFFC-4EFC-97F8-20A126FA1709', '0' , '0.jpg');
		$this->assertTrue(true);
	}*/
}
