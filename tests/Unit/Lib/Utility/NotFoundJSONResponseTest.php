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

namespace OCA\Passman\Tests\Unit\Lib\Utility;

use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\Http;
use Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\OCA\Passman\Utility\NotFoundJSONResponse::class)]
class NotFoundJSONResponseTest extends TestCase {
	public function testOnEmptyResponse(): void {
		$data = new NotFoundJSONResponse();
		$this->assertEquals(Http::STATUS_NOT_FOUND, $data->getStatus());
		$this->assertJsonStringEqualsJsonString('[]', $data->render(), 'Expected empty JSON response');
	}

	public function testOnDataResult(): void {
		$data = [
			'field' => 'value',
			'boolean' => true,
			'integer' => 21,
		];
		$response = new NotFoundJSONResponse($data);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
		$this->assertJsonStringEqualsJsonString(json_encode($data), $response->render(), 'Rendered data does not match with expected data');
	}
}
