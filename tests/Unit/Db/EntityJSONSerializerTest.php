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

namespace OCA\Passman\Tests\Unit\Db;

use OCA\Passman\Db\EntityJSONSerializer;
use PHPUnit\Framework\Attributes\CoversNothing;
use Test\TestCase;

#[CoversNothing]
class EntityJSONSerializerTest extends TestCase {
	private const TEST_FIELDS = [
		'an_string'       => 'value',
		'an_int'          => 1234,
		'a_bool'          => true,
		'null'            => null,
		'a_double'        => 4.563,
		'an_int_array'    => [1, 32, 55, 134],
		'an_string_array' => ['asdf', 'fdsa'],
	];

	protected EntityJSONSerializerTestStub $serializer;

	protected function setUp(): void {
		parent::setUp();
		$this->serializer = new EntityJSONSerializerTestStub(self::TEST_FIELDS);
	}

	public function testSerializeFieldsFull(): void {
		$this->assertEquals(self::TEST_FIELDS, $this->serializer->serializeFields(array_keys(self::TEST_FIELDS)));
	}

	public function testSerializeFieldsPartial(): void {
		$fields = ['an_string', 'an_int', 'an_int_array'];
		$expectedData = [];
		foreach ($fields as $value) {
			$expectedData[$value] = self::TEST_FIELDS[$value];
		}
		$this->assertEquals($expectedData, $this->serializer->serializeFields($fields));
	}
}

class EntityJSONSerializerTestStub {
	use EntityJSONSerializer;

	public string $an_string;
	public int    $an_int;
	public bool   $a_bool;
	public mixed  $null;
	public float  $a_double;
	/** @var list<int> */
	public array $an_int_array;
	/** @var list<string> */
	public array $an_string_array;

	public function __construct(array $fields) {
		$this->an_string = $fields['an_string'];
		$this->an_int = $fields['an_int'];
		$this->a_bool = $fields['a_bool'];
		$this->null = $fields['null'];
		$this->a_double = $fields['a_double'];
		$this->an_int_array = $fields['an_int_array'];
		$this->an_string_array = $fields['an_string_array'];
	}
}
