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

use \OCA\Passman\Db\Credential;
use \OCP\AppFramework\Db\Entity;

/**
 * @coversDefaultClass \OCA\Passman\Db\Credential
 * @uses \OCA\Passman\Db\EntityJSONSerializer
 * @uses \OCP\AppFramework\Db\Entity
 * @uses \JsonSerializable
 */
class CredentialTest extends PHPUnit_Framework_TestCase {
	CONST TEST_DATA = [
		'id' 		=> 5,
		'guid' 		=> 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'vault_id'	=> 1,
		'user_id'	=> 'WolFi',
		'label'		=> 'Test credential for unit testing',
		'description' => 'eyJpdiI6InhoSVczaEpvUVpoMmo0TWpibkQ1ZEEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiSjB0YkpBUjdMY0pSUGFEaSJ9',
		'created'	=> 1475963590,
		'changed'	=> 1475963600,
		'tags'		=> 'eyJpdiI6InZ5NjUwbEtvejNPa09TOWZuWEs4OVEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoibXlqQnNBSnFWbFVrSlEifQ==',
		'email'		=> 'eyJpdiI6ImZCblR4S2FuSFBiNGlXWmYxZlBTcmciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiQ0lTSWRNSnE2QzVIbG8zdiJ9',
		'username'	=> 'eyJpdiI6IlVGUStYNkZTUEYwU3pvd2Z1NUxFcVEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoidFhGTGcrU3RML0Y2dUE4dSJ9',
		'password'	=> 'eyJpdiI6Ik5Bakc0bVY5SEtBbW5tb1AwTEhKZFEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiNEhyWTJHUGtkTEs1V0tuYXFtUXBjWXZvSkFqSyJ9',
		'url'		=> 'eyJpdiI6IkFVZ0RyWFA4S1lwVHl6WGZkK1U3RWciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiQ2NiUUtHQlRmbkYveU5BMyJ9',
		'favicon'	=> null,
		'renew_interval' => null,
		'expire_time' => 0,
		'delete_time' => 0,
		'files'		=> 'eyJpdiI6Ink4UkorSWdSYmthZGdUVEoyKzArT1EiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiNXpidHJJaE5kSCs4MHcifQ==',
		'custom_fields' => 'eyJpdiI6InJCdlNNNmhINHM4OG9NUld1eTNaTHciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiZDJaRUFiSWZ5a1FJRXcifQ==',
		'otp' 		=> 'eyJpdiI6IjBzL0g0YUZvWVRaN2RFMlhETS9aMnciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiMGsvckljcGJwMWU1SVEifQ==',
		'hidden'	=> 1,
		'shared_key'=> null
	];

	/**
	 * @var Credential
	 */
	protected $credential;

	/**
	 * @after
	 */
	public function setUp() {
		$this->credential = Credential::fromRow(self::TEST_DATA);
	}

	/**
	 * @coversNothing
	 */
	public function testInstances() {
		$this->assertTrue($this->credential instanceof Entity);
		$this->assertTrue($this->credential instanceof \JsonSerializable);
	}

	/**
	 * @covers ::__construct
	 * @covers ::getter
	 * @covers ::fromRow
	 */
	public function testGetters(){
		$this->assertEquals(self::TEST_DATA['id'], $this->credential->getId());
		$this->assertEquals(self::TEST_DATA['guid'], $this->credential->getGuid());
		$this->assertEquals(self::TEST_DATA['vault_id'], $this->credential->getVaultId());
		$this->assertEquals(self::TEST_DATA['user_id'], $this->credential->getUserId());
		$this->assertEquals(self::TEST_DATA['label'], $this->credential->getLabel());
		$this->assertEquals(self::TEST_DATA['description'], $this->credential->getDescription());
		$this->assertEquals(self::TEST_DATA['created'], $this->credential->getCreated());
		$this->assertEquals(self::TEST_DATA['changed'], $this->credential->getChanged());
		$this->assertEquals(self::TEST_DATA['tags'], $this->credential->getTags());
		$this->assertEquals(self::TEST_DATA['email'], $this->credential->getEmail());
		$this->assertEquals(self::TEST_DATA['username'], $this->credential->getUsername());
		$this->assertEquals(self::TEST_DATA['password'], $this->credential->getPassword());
		$this->assertEquals(self::TEST_DATA['url'], $this->credential->getUrl());
		$this->assertEquals(self::TEST_DATA['favicon'], $this->credential->getFavicon());
		$this->assertEquals(self::TEST_DATA['renew_interval'], $this->credential->getRenewInterval());
		$this->assertEquals(self::TEST_DATA['expire_time'], $this->credential->getExpireTime());
		$this->assertEquals(self::TEST_DATA['delete_time'], $this->credential->getDeleteTime());
		$this->assertEquals(self::TEST_DATA['files'], $this->credential->getFiles());
		$this->assertEquals(self::TEST_DATA['custom_fields'], $this->credential->getCustomFields());
		$this->assertEquals(self::TEST_DATA['otp'], $this->credential->getOtp());
		$this->assertEquals(self::TEST_DATA['hidden'], $this->credential->getHidden());
		$this->assertEquals(self::TEST_DATA['shared_key'], $this->credential->getSharedKey());
	}

	/**
	 * @covers ::setter
	 */
	public function testSetters() {
		/**
		 * Only testing one setter since if it works all setters should work because php magic.
		 * please, if you override a setter implement it here.
		 */
		$this->credential->setUserId('Sander');
		$this->assertTrue($this->credential->getUserId() === 'Sander');
	}

	/**
	 * @covers ::jsonSerialize
	 */
	public function testJsonSerialize(){
		$json_array = $this->credential->jsonSerialize();

		$comparision_array = [
			'credential_id' => self::TEST_DATA['id'],
			'guid'				=> self::TEST_DATA['guid'],
			'user_id' 			=> self::TEST_DATA['user_id'],
			'vault_id' 			=> self::TEST_DATA['vault_id'],
			'label' 			=> self::TEST_DATA['label'],
			'description' 		=> self::TEST_DATA['description'],
			'created' 			=> self::TEST_DATA['created'],
			'changed' 			=> self::TEST_DATA['changed'],
			'tags' 				=> self::TEST_DATA['tags'],
			'email' 			=> self::TEST_DATA['email'],
			'username' 			=> self::TEST_DATA['username'],
			'password' 			=> self::TEST_DATA['password'],
			'url' 				=> self::TEST_DATA['url'],
			'favicon' 			=> self::TEST_DATA['favicon'],
			'renew_interval' 	=> self::TEST_DATA['renew_interval'],
			'expire_time' 		=> self::TEST_DATA['expire_time'],
			'delete_time' 		=> self::TEST_DATA['delete_time'],
			'files' 			=> self::TEST_DATA['files'],
			'custom_fields' 	=> self::TEST_DATA['custom_fields'],
			'otp' 				=> self::TEST_DATA['otp'],
			'hidden' 			=> self::TEST_DATA['hidden'],
			'shared_key'		=> self::TEST_DATA['shared_key'],
		];

		$this->assertEquals($comparision_array, $json_array);
	}
}