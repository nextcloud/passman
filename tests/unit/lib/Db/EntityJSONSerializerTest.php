<?php

/**
 * Test case for the EntityJSONSerializer trait
 * Date: 9/10/16
 * Time: 18:04
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
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