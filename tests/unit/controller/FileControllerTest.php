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
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Tests\PassmanTestCase;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FileControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\FileController
 */
class FileControllerTest extends PassmanTestCase
{

    private FileController $controller;
    private string $userId = 'example';
    private EncryptService $encryptService;
    private FileService $fileService;
    private FileMapper $fileMapper;
    private LoggerInterface $logger;
    private SettingsService $settings;


    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function setUp(): void
    {
        $this->appContainer->registerService('UserId', function ($c) {
            return $this->userId;
        });

        $config = $this->appContainer->get(IConfig::class);
        $request = $this->appContainer->get(IRequest::class);

        $this->fileMapper = $this->appContainer->get(FileMapper::class);
        $this->settings = new SettingsService($this->userId, $config, self::APP_NAME);

        $this->encryptService = new EncryptService($this->settings, $config);
        $this->fileService = new FileService($this->fileMapper, $this->encryptService, $config);
        $this->logger = $this->appContainer->get(LoggerInterface::class);
        $this->controller = new FileController(
            self::APP_NAME,
            $request,
            $this->userId,
            $this->fileService,
            $this->logger
        );
    }

    /**
     * @covers ::uploadFile
     */
    public function testUploadFile()
    {
        $result = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertNotNull($result->getData());
    }

    /**
     * @covers ::getFile
     */
    public function testGetExistingFile()
    {
        $uploadResult = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
        $this->assertTrue($uploadResult instanceof JSONResponse);
        $this->assertNotNull($uploadResult->getData());

        $result = $this->controller->getFile($uploadResult->getData()->id);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertNotNull($result->getData());
    }

    /**
     * @covers ::getFile
     */
    public function testGetUnspecifiedFile()
    {
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
    public function testGetUnknownFile()
    {
        try {
            $this->controller->getFile(100000);
            $this->fail('Getting a not existing file should ever fail');
        } catch (DoesNotExistException $exception) {
            $this->assertNotNull($exception->getMessage());
        }
    }

    /**
     * @covers ::deleteFile
     */
    public function testDeleteFile()
    {
        $uploadResult = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
        $this->assertTrue($uploadResult instanceof JSONResponse);
        $this->assertNotNull($uploadResult->getData());

        $result = $this->controller->deleteFile($uploadResult->getData()->id);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertNotNull($result->getData());
    }

    public function testDeleteUnspecifiedFile()
    {
        try {
            $this->controller->getFile(null);
            $this->fail('Deleting an unspecified file should ever fail');
        } catch (\TypeError $exception) {
            $this->assertStringContainsString('type int', $exception->getMessage());
        }
    }

    /**
     * @covers ::updateFile
     */
    public function testUpdateFile()
    {
        $data = '000111222';
        $updateData = '111222333444';
        $uploadResult = $this->controller->uploadFile($data, '0.png', 'image/png', strlen($data));
        $this->assertTrue($uploadResult instanceof JSONResponse);
        $this->assertNotNull($uploadResult->getData());

        $this->controller->updateFile($uploadResult->getData()->id, $updateData, '0.jpg');
        /** @var File $updatedFile */
        $updatedFile = $this->fileService->getFile($uploadResult->getData()->id, $this->userId);
        $this->assertTrue($updatedFile->getFileData() === $updateData);
    }
}
