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
use OCA\Passman\Controller\TranslationController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\OCA\Passman\Controller\TranslationController::class)]
class TranslationControllerTest extends TestCase {
	private TranslationController $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->controller = new TranslationController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->createMock(IL10N::class),
		);
	}

	public function testGetLanguageStrings(): void {
		$result = $this->controller->getLanguageStrings(null);
		$this->assertInstanceOf(JSONResponse::class, $result);
	}
}
