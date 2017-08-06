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

use OCA\Passman\Service\IconService;
use PHPUnit_Framework_TestCase;
use OCA\Passman\Service\SettingsService;

/**
 * Class SettingsServiceTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass  \OCA\Passman\Service\EncryptService
 */
class IconServiceTest extends PHPUnit_Framework_TestCase {

	private $service;
	private $testKey;

	public function setUp() {
		$this->options = array(
			'sslVerify' => false,
		);

	}

	/**
	 * @covers ::urlType
	 */
	public function testUrlType()
	{
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE, IconService::urlType('http://www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_SCHEME, IconService::urlType('//www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_PATH, IconService::urlType('/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_RELATIVE, IconService::urlType('../images/fav.ico'));
	}

	/**
	 * @covers ::getExtensionFromMimeType
	 */
	public function testGetExtMime()
	{
		$this->assertEquals('ico', IconService::getExtensionFromMimeType('image/x-icon'));
		$this->assertEquals('png', IconService::getExtensionFromMimeType('image/png'));
		$this->assertEquals('gif', IconService::getExtensionFromMimeType('image/gif'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpeg'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpg'));
	}

	/**
	 * Website without favicon
	 */
	public function testNoFavicon()
	{
		$fav = new IconService('http://example.org/', $this->options);
		$this->assertNull($fav->icoData);
		$this->assertNotNull($fav->error);
		$this->assertFalse(isset($fav->debugInfo['failover']));
		$this->assertEquals(404, $fav->debugInfo['favicon_download_metadata']['http_code']);
	}

	/**
	 * Website using default favicon (/favicon.ico)
	 */
	public function testDefaultFavicon()
	{
		$fav = new IconService('https://getcomposer.org/', $this->options);
		$this->assertNotNull($fav->icoData);
		$this->assertNull($fav->error);
		$this->assertFalse(isset($fav->debugInfo['failover']));
		$this->assertEquals(200, $fav->debugInfo['favicon_download_metadata']['http_code']);
		$this->assertEquals('https://getcomposer.org/favicon.ico', $fav->icoUrl);
		$this->assertEquals('default', $fav->findMethod);
	}

	/**
	 * Custom favicon URL (head.link), absolute URL
	 */
	public function testHeadAbsoluteWithoutBaseHref()
	{
		$fav = new IconService('https://code.google.com/p/chromium/issues/detail?id=236848', $this->options);
		$this->assertNotNull($fav->icoData);
		$this->assertNull($fav->error);
		$this->assertFalse(isset($fav->debugInfo['failover']));
		$this->assertEquals(200, $fav->debugInfo['favicon_download_metadata']['http_code']);
		$this->assertEquals('https://bugs.chromium.org/static/images/monorail.ico', $fav->icoUrl);
		$this->assertEquals('head absolute_path without base href', $fav->findMethod);
	}

	/**
	 * Custom favicon URL (head.link), absolute path
	 */
	public function testHeadAbsolute()
	{
		$fav = new IconService('http://www.koreus.com/', $this->options);
		$this->assertNotNull($fav->icoData);
		$this->assertNull($fav->error);
		$this->assertFalse(isset($fav->debugInfo['failover']));
		$this->assertEquals(200, $fav->debugInfo['favicon_download_metadata']['http_code']);
		$this->assertEquals('https://koreus.cdn.li/static/images/koreus-58x58.png', $fav->icoUrl);
		$this->assertEquals('head absolute', $fav->findMethod);
	}

}