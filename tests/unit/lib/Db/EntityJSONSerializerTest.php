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

use \OCA\Passman\Db\EntityJSONSerializer;

/**
 * @coversDefaultClass \OCA\Passman\Db\EntityJSONSerializer
 */
class EntityJSONSerializerTest extends PHPUnit_Framework_TestCase {
	CONST TEST_FIELDS = [
		'an_string'	=> 'value',
		'an_int'	=> 1234,
		'a_bool'	=> true,
		'null'		=> null,
		'a_double'	=> 4.563,
		'an_int_array'		=> [1, 32, 55, 134],
		'an_string_array'	=> ['asdf', 'fdsa']
	];

	/**
	 * @var EntityJSONSerializer
	 */
	protected $trait;

	public function setUp() {
		$this->trait = $this->getObjectForTrait(EntityJSONSerializer::class);

		foreach (self::TEST_FIELDS as $key => $value){
			$this->trait->$key = $value;
		}
	}

	/**
	 * @covers ::serializeFields
	 */
	public function testSerializeFieldsFull(){
		$actual_data = $this->trait->serializeFields(array_keys(self::TEST_FIELDS));
		$this->assertEquals(self::TEST_FIELDS, $actual_data);
	}

	/**
	 * @covers ::serializeFields
	 */
	public function testSerializeFieldsPartial(){
		$fields = ['an_string', 'an_int', 'an_int_array'];
		$actual_data = $this->trait->serializeFields($fields);
		$expected_data = [];
		foreach ($fields as $value){
			$expected_data[$value] = self::TEST_FIELDS[$value];
		}
		$this->assertEquals($expected_data, $actual_data);
	}
}