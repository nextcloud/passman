<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

declare(strict_types=1);

namespace OCA\Passman\Tests\Unit\Controller;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Controller\FileController;
use OCA\Passman\Db\File;
use OCA\Passman\Service\FileService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Test\TestCase;

#[CoversClass(FileController::class)]
class FileControllerTest extends TestCase {
	private FileController $controller;
	private FileService    $fileService;

	protected function setUp(): void {
		parent::setUp();

		$this->fileService = $this->createMock(FileService::class);

		$this->controller = new FileController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			'example',
			$this->fileService,
			$this->createMock(LoggerInterface::class),
		);
	}

	public function testUploadFile(): void {
		$this->fileService->method('createFile')->willReturn(new File());
		$result = $this->controller->uploadFile('000', '0.png', 'image/png', 3);
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	public function testGetFile(): void {
		$this->fileService->method('getFile')->with(1, 'example')->willReturn(new File());
		$result = $this->controller->getFile(1);
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	public function testDeleteFile(): void {
		$this->fileService->method('deleteFile')->with(1, 'example')->willReturn(new File());
		$result = $this->controller->deleteFile(1);
		$this->assertInstanceOf(JSONResponse::class, $result);
	}

	public function testUpdateFile(): void {
		$file = new File();
		$this->fileService->method('getFile')->with(1, 'example')->willReturn($file);
		$this->fileService->method('updateFile')->willReturn($file);
		$this->controller->updateFile(1, '0', '0.jpg');
		$this->addToAssertionCount(1);
	}
}
