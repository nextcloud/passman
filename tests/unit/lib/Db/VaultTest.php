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

use \OCA\Passman\Db\Vault;

/**
 * @coversDefaultClass  \OCA\Passman\Db\Vault
 */
class VaultTest extends PHPUnit_Framework_TestCase {
	CONST TEST_DATA = [
		'id'						=> 1,
		'guid'						=> 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'name'						=> 'Test example vault',
		'user_id'					=> 'WolFi',
		'created'					=> 1475793617,
		'last_access'				=> 1475853174,
		'public_sharing_key'		=>
'-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcfpaaZRS9Tlc3ucSixhqgO7lA
x0jO3kvk4JayFRMASf/4TMLhBNlSJgdwiSJQ+yPksnE2kGwqK5TMzn7hsAujnrfS
b/bxP0kcGb77JRhx0mjgHqfI/EZDHHtKBMpnejrmCdHfMcSi/LjYJFFMozZ3dqIv
pbbPwdc88kGcdqBzWQIDAQAB
-----END PUBLIC KEY-----',
		'private_sharing_key'		=> 'eyJpdiI6ImhEN1F3bFBOU3gyRHpOZHpJTjJBanciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoidUh2ejhWQUtqRmpqSDJWRFVCcnNwVDcyemhDVVNEVWdkandSR1ExdDlMSzVjRHIwRXZHek9JempQQW10RWE5R0hEMlRYVTBwUE9wVTg4ZkVaeXhJdERNK2dMaERZSzljZDZXZnB2QmJOcGlIN2NqZ1NKVERGd1FaOTh5UVlIRERBQnJsVHRrdjJtWjljYW9aLzQ2ZlFoaHM3RldGRVkweS9zelJzT1VWV2twRVhwZVVpQnIwVFBWVDNyQzNSMjVkRmdHNWoyT2krd3JPazBNUFQ3T1lMUkhjd2NWMTcvTWQrRXF3bXZMZDdBUHAzU2dIRVVUbTdNRW1ST1VDU3h6RHhqWldaSFlsZGllalczVkN6bElqdElHOW1qcUJqTFRmS0UxVlZIQkRoaDFFMGFycVVtRHpiRmJ5K0tzOXB1UE5ueEZjVjYrMGNSM1Nkc2pKY3U2ZFExRVU3Vk9PeFV6SGdKVzV5RmtKZjZvdmJYSEwraTFNeUhvSUFteWloSVdKdDB3NHRlM2N3WEd5NURybHU2MUFvbHhmeTFjYmJPRW9FaDBad0xLVnBIV0JHTmREL01QOS9XZ3A1U0t6MjBabjdZNWpxeXJjOW1YQUNFWnBQVXFibWNNbVE5RGZLTEtoQ1ROTUM5cWE1a3RpblgvVWxqODN6aTQyZ1JYVEJxUXhtSVBYcVpzem0zTXBaVGxLMmpFMVZudGpGcThmbTBFRmFsSkFoRUk2RlNOYnlhY0JPTFUwYU1UU1BPRTEzL3NWTzFCZFFjSXBTbmY0cmJ6blBveEZHY1BYYzBtM2VXbUJqSWpJd3pzZjlyMHRaRllTNWpta0YwTWs1ZXRZdmZDT0JLWVd2U2NTSzlRMTdqSHBLd2plSjFwSkZGRk13VGdlTGZiRDVyR2xSaitaVENvb3FnSlcrZFhMTk9iaVhuWGVqZkFWNS9JeGdYTnltdXd4MmR3NUdjeHo0QTViSlBOWHNURGgxZDZVcHRZK2RDcUk2Zy9pRmlieUwwTk14Y1VhZGhDaHhkTTc3TFlCR1Yrdkt5M2hFQm9XeEhzYWdlQ1ZMV2tHRTBLNEV2aFA2MjFlRWNucjlRN3QyaGtiTlVjLzJIbEZIMUV4RjZvOWsvM2RQa0FFeUxVMmVWTHAvUWtYMDMySFJzN0J5bko0Y2hsMjBwemNRZzNPdlpvVXRudE8yaWlFTlQwUW5DeVBNeVc3TlVyaUNXT0dldmtlUEt3YVIvQVNLRXQ1aWg2Z3hGYU5sL0hJSXpyS3RWMk9kYWpBVjVtSlROM1VpTFhjTWRXUUxoVHhFaG9EeWwrZWQ1QitUZnlvWVUxYzFmbmpidU1wSGdRaW1YdXlhSlJLY0MxWVBTWmNtdUErZFNJSTlnU0ZxZlNEQ01BZnAzNTY4ZHFLMGFqMkRybTIxNGlvVDRqakFnWUc5cGRPY0xqZFpyS3MxME0xdkVNc21VbEh0aHViQkZmd3IvUnkzdWpmNXBHTWZtbk9uVHlaUUtuMVJTT08zUS9veWxiK2t1bjBoVVZYNm1rTVJ6OHRpNjZocDEzVVVndjJzSk9QUDN0L1V2ZFFBUzF3QTVrSy9OOG5YRUtMVWgrNC8xVVlqQVhER0EifQ==',
		'sharing_keys_generated'	=> 1475793617,
		'vault_settings'			=> null,
	];

	/**
	 * @var Vault
	 */
	protected $vault;

	/**
	 * @after
	 */
	public function setUp() {
		$this->vault = Vault::fromRow(self::TEST_DATA);
	}

	/**
	 * @covers ::getter
	 * @covers ::__construct
	 * @covers ::fromRow
	 */
	public function testGetters(){
		$this->assertEquals(self::TEST_DATA['id'], $this->vault->getId());
		$this->assertEquals(self::TEST_DATA['guid'], $this->vault->getGuid());
		$this->assertEquals(self::TEST_DATA['name'], $this->vault->getName());
		$this->assertEquals(self::TEST_DATA['user_id'], $this->vault->getUserId());
		$this->assertEquals(self::TEST_DATA['created'], $this->vault->getCreated());
		$this->assertEquals(self::TEST_DATA['last_access'], $this->vault->getLastAccess());
		$this->assertEquals(self::TEST_DATA['public_sharing_key'], $this->vault->getPublicSharingKey());
		$this->assertEquals(self::TEST_DATA['private_sharing_key'], $this->vault->getPrivateSharingKey());
		$this->assertEquals(self::TEST_DATA['sharing_keys_generated'], $this->vault->getSharingKeysGenerated());
		$this->assertEquals(self::TEST_DATA['vault_settings'], $this->vault->getVaultSettings());
	}

	/**
	 * @covers ::setter
	 */
	public function testSetters(){
		/**
		 * Only testing one setter since if it works all setters should work because php magic.
		 * please, if you override a setter implement it here.
		 */
		$this->vault->setVaultSettings('{json:"object"}');
		$this->assertEquals('{json:"object"}', $this->vault->getVaultSettings());
		// Reset values to test data
		$this->setUp();
	}

	/**
	 * @coversNothing
	 */
	public function testInheritedExpectedClasses() {
		$this->assertInstanceOf(\JsonSerializable::class, $this->vault);
		$this->assertInstanceOf(\OCP\AppFramework\Db\Entity::class, $this->vault);
	}

	/**
	 * @covers ::jsonSerialize
	 */
	public function testJsonSerialize() {
		$expected_data = [
			'vault_id' => self::TEST_DATA['id'],
			'guid' => self::TEST_DATA['guid'],
			'name' => self::TEST_DATA['name'],
			'created' => self::TEST_DATA['created'],
			'public_sharing_key' => self::TEST_DATA['public_sharing_key'],
			'last_access' => self::TEST_DATA['last_access'],
		];

		$actual_data = $this->vault->jsonSerialize();

		$this->assertEquals($expected_data, $actual_data);
	}
}