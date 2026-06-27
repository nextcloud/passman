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

use OCA\Passman\Controller\PageController;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\TestCase;

#[CoversClass(PageController::class)]
class PageControllerTest extends TestCase {
	private PageController $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->controller = new PageController($this->createMock(IRequest::class));
	}

	public function testIndex(): void {
		$result = $this->controller->index();
		$this->assertEquals([], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertInstanceOf(TemplateResponse::class, $result);
	}

	public function testBookmarklet(): void {
		$result = $this->controller->bookmarklet('https://google.com', 'Google');
		$this->assertEquals(['url' => 'https://google.com', 'title' => 'Google'], $result->getParams());
		$this->assertEquals('bookmarklet', $result->getTemplateName());
		$this->assertInstanceOf(TemplateResponse::class, $result);
	}

	public function testPublicSharePage(): void {
		$result = $this->controller->publicSharePage();
		$this->assertEquals('public_share', $result->getTemplateName());
		$this->assertInstanceOf(TemplateResponse::class, $result);
	}
}
