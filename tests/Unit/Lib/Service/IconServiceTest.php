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
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[CoversClass(IconService::class)]
class IconServiceTest extends TestCase {
	// 1x1 transparent PNG
	private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

	private IClient&MockObject $client;

	/** @var string[] URLs requested through the client, in order */
	private array $requestedUrls = [];

	protected function setUp(): void {
		parent::setUp();
		$this->client = $this->createMock(IClient::class);
		$this->requestedUrls = [];
	}

	private function response(int $status, string $body, string $contentType): IResponse {
		$response = $this->createStub(IResponse::class);
		$response->method('getStatusCode')->willReturn($status);
		$response->method('getBody')->willReturn($body);
		$response->method('getHeader')->willReturnCallback(
			static fn(string $key): string => strtolower($key) === 'content-type' ? $contentType : ''
		);
		return $response;
	}

	/**
	 * @param array<string, IResponse> $responses responses by URL, unknown URLs get a 404
	 */
	private function serve(array $responses): void {
		$this->client->method('get')->willReturnCallback(
			function (string $url) use ($responses): IResponse {
				$this->requestedUrls[] = $url;
				return $responses[$url] ?? $this->response(404, '', 'text/html');
			}
		);
	}

	public function testUrlType(): void {
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE, IconService::urlType('https://www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_SCHEME, IconService::urlType('//www.domain.com/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_ABSOLUTE_PATH, IconService::urlType('/images/fav.ico'));
		$this->assertEquals(IconService::URL_TYPE_RELATIVE, IconService::urlType('../images/fav.ico'));
	}

	public function testGetExtMime(): void {
		$this->assertEquals('ico', IconService::getExtensionFromMimeType('image/x-icon'));
		$this->assertEquals('png', IconService::getExtensionFromMimeType('image/png'));
		$this->assertEquals('gif', IconService::getExtensionFromMimeType('image/gif'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpeg'));
		$this->assertEquals('jpg', IconService::getExtensionFromMimeType('image/jpg'));
	}

	public static function forbiddenSchemeProvider(): array {
		return [
			'ftp'    => ['ftp://internal.example/favicon.ico'],
			'file'   => ['file:///etc/passwd'],
			'gopher' => ['gopher://127.0.0.1:6379/_INFO'],
			'dict'   => ['dict://127.0.0.1:11211/stats'],
		];
	}

	#[DataProvider('forbiddenSchemeProvider')]
	public function testConstructorRejectsNonHttpSchemesWithoutRequest(string $url): void {
		$this->client->expects($this->never())->method('get');

		$this->expectException(\InvalidArgumentException::class);
		new IconService($this->client, $url);
	}

	#[DataProvider('forbiddenSchemeProvider')]
	public function testDownloadAsRejectsNonHttpSchemesWithoutRequest(string $url): void {
		$this->client->expects($this->never())->method('get');
		$service = new IconService($this->client, 'https://example.com/', false);

		$info = [];
		$this->assertFalse($service->downloadAs($url, $info));
		$this->assertNotNull($info['error']);
	}

	public function testDownloadAsReturnsFalseWhenClientBlocksLocalAddress(): void {
		// The Nextcloud client throws this for local/private targets (allow_local_remote_servers off)
		$this->client->expects($this->once())
			->method('get')
			->with('http://169.254.169.254/latest/meta-data/')
			->willThrowException(new LocalServerException('Host "169.254.169.254" violates local access rules'));
		$service = new IconService($this->client, 'http://169.254.169.254/latest/meta-data/', false);

		$info = [];
		$this->assertFalse($service->downloadAs('http://169.254.169.254/latest/meta-data/', $info));
		$this->assertNotNull($info['error']);
	}

	public function testBlockedLocalAddressYieldsNoIcon(): void {
		$this->client->method('get')->willThrowException(new LocalServerException('local'));

		$service = new IconService($this->client, 'http://127.0.0.1:6379/');

		$this->assertFalse($service->icoExists);
		$this->assertNull($service->icoData);
	}

	public function testNonImageResponseIsNeverExposed(): void {
		// Even if a request to an internal service gets through (e.g. allow_local_remote_servers
		// enabled), a non-image body must not be handed out, whatever Content-Type it claims.
		$secret = '{"AccessKeyId":"ASIA-SECRET","SecretAccessKey":"very-secret"}';
		$this->serve([
			'http://169.254.169.254/latest/meta-data/' => $this->response(200, $secret, 'application/json'),
			'http://169.254.169.254/favicon.ico'       => $this->response(200, $secret, 'image/x-icon'),
		]);

		$service = new IconService($this->client, 'http://169.254.169.254/latest/meta-data/');

		$this->assertFalse($service->icoExists);
		$this->assertNull($service->icoData);
	}

	public function testFetchesLinkedImageThroughClient(): void {
		$png = base64_decode(self::PNG);
		$this->serve([
			'https://example.com/'        => $this->response(200, '<html><head><link rel="icon" href="/fav.png"></head><body></body></html>', 'text/html'),
			'https://example.com/fav.png' => $this->response(200, $png, 'image/png'),
		]);

		$service = new IconService($this->client, 'https://example.com/');

		$this->assertTrue($service->icoExists);
		$this->assertSame($png, $service->icoData);
		$this->assertSame('png', $service->icoType);
		$this->assertSame(['https://example.com/', 'https://example.com/fav.png'], $this->requestedUrls);
	}

	public function testLinkedIconWithForbiddenSchemeIsNotFetched(): void {
		// The icon URL from the page's <link> tag is attacker controlled, too
		$this->serve([
			'https://example.com/' => $this->response(200, '<html><head><link rel="icon" href="ftp://internal.example/fav.ico"></head><body></body></html>', 'text/html'),
		]);

		$service = new IconService($this->client, 'https://example.com/');

		$this->assertFalse($service->icoExists);
		$this->assertSame(['https://example.com/'], $this->requestedUrls);
	}

	public function testFetchesSvgIcon(): void {
		$svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<!-- logo -->' . "\n"
			. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>';
		$this->serve([
			'https://example.com/'         => $this->response(200, '<html><head><link rel="icon" href="/icon.svg"></head><body></body></html>', 'text/html'),
			'https://example.com/icon.svg' => $this->response(200, $svg, 'image/svg+xml'),
		]);

		$service = new IconService($this->client, 'https://example.com/');

		$this->assertTrue($service->icoExists);
		$this->assertSame($svg, $service->icoData);
		$this->assertSame('svg+xml', $service->icoType);
	}

	public static function svgProvider(): array {
		return [
			'plain'            => ['<svg xmlns="http://www.w3.org/2000/svg"></svg>', true],
			'self closing'     => ['<svg/>', true],
			'xml declaration'  => ['<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg"/>', true],
			'comment'          => ["<!-- Generator: Some Editor -->\n<svg xmlns=\"http://www.w3.org/2000/svg\"></svg>", true],
			'doctype'          => ['<?xml version="1.0"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg></svg>', true],
			'bom + whitespace' => ["\xEF\xBB\xBF\n  <svg>\n</svg>", true],
			'other xml'        => ['<?xml version="1.0"?><config><password>secret</password></config>', false],
			'html with svg'    => ['<html><body><svg></svg>secret</body></html>', false],
			'svg-ish element'  => ['<foosvg>secret</foosvg>', false],
			'json'             => ['{"svg":"<svg></svg>"}', false],
		];
	}

	#[DataProvider('svgProvider')]
	public function testIsSvg(string $content, bool $expected): void {
		$this->assertSame($expected, IconService::isSvg($content));
		$this->assertSame($expected, IconService::isImage($content));
	}

	public function testIsImage(): void {
		$this->assertTrue(IconService::isImage(base64_decode(self::PNG)));
		$this->assertFalse(IconService::isImage('{"some":"json"}'));
		$this->assertFalse(IconService::isImage('<html><body>internal page</body></html>'));
		$this->assertFalse(IconService::isImage(''));
	}
}
