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

namespace OCA\Passman\Tests\Unit\Lib\Service;

use OCA\Passman\Service\IconService;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Service\IconService
 */
class IconServiceTest extends TestCase {
	/** @covers ::urlType */
	public function testUrlType(): void {
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE, IconService::urlType('http://www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_SCHEME, IconService::urlType('//www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_PATH, IconService::urlType('/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_RELATIVE, IconService::urlType('../images/fav.ico'));
	}

	/** @covers ::getExtensionFromMimeType */
	public function testGetExtMime(): void {
		$this->assertEquals('ico', IconService::getExtensionFromMimeType('image/x-icon'));
		$this->assertEquals('png', IconService::getExtensionFromMimeType('image/png'));
		$this->assertEquals('gif', IconService::getExtensionFromMimeType('image/gif'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpeg'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpg'));
	}
}
