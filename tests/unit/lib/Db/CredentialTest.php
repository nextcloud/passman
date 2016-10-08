<?php

/**
 * Test case for the Database Credential model class
 * Date: 8/10/16
 * Time: 23:41
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
 */
use \OCA\Passman\Db\Credential;
use \OCP\AppFramework\Db\Entity;

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

	public function setUp() {
		$this->credential = Credential::fromRow(self::TEST_DATA);
	}

	public function testInstances() {
		$this->assertTrue($this->credential instanceof Entity);
		$this->assertTrue($this->credential instanceof \JsonSerializable);
	}

	public function testGetters(){
		$this->assertTrue($this->credential->getId() 		=== self::TEST_DATA['id']);
		$this->assertTrue($this->credential->getGuid() 		=== self::TEST_DATA['guid']);
		$this->assertTrue($this->credential->getVaultId() 	=== self::TEST_DATA['vault_id']);
		$this->assertTrue($this->credential->getUserId() 	=== self::TEST_DATA['user_id']);
		$this->assertTrue($this->credential->getLabel()		=== self::TEST_DATA['label']);
		$this->assertTrue($this->credential->getDescription() === self::TEST_DATA['description']);
		$this->assertTrue($this->credential->getCreated()	=== self::TEST_DATA['created']);
		$this->assertTrue($this->credential->getChanged()	=== self::TEST_DATA['changed']);
		$this->assertTrue($this->credential->getTags()		=== self::TEST_DATA['tags']);
		$this->assertTrue($this->credential->getEmail()		=== self::TEST_DATA['email']);
		$this->assertTrue($this->credential->getUsername()	=== self::TEST_DATA['username']);
		$this->assertTrue($this->credential->getPassword()	=== self::TEST_DATA['password']);
		$this->assertTrue($this->credential->getUrl()		=== self::TEST_DATA['url']);
		$this->assertTrue($this->credential->getFavicon()	=== self::TEST_DATA['favicon']);
		$this->assertTrue($this->credential->getRenewInterval() === self::TEST_DATA['renew_interval']);
		$this->assertTrue($this->credential->getExpireTime() === self::TEST_DATA['expire_time']);
		$this->assertTrue($this->credential->getDeleteTime() === self::TEST_DATA['delete_time']);
		$this->assertTrue($this->credential->getFiles()		=== self::TEST_DATA['files']);
		$this->assertTrue($this->credential->getCustomFields() === self::TEST_DATA['custom_fields']);
		$this->assertTrue($this->credential->getOtp()		=== self::TEST_DATA['otp']);
		$this->assertTrue($this->credential->getHidden()	=== self::TEST_DATA['hidden']);
		$this->assertTrue($this->credential->getSharedKey() === self::TEST_DATA['shared_key']);
	}

	public function testSetters() {
		/**
		 * Only testing one setter since if it works all setters should work because php magic.
		 * please, if you override a setter implement it here.
		 */
		$this->credential->setUserId('Sander');
		$this->assertTrue($this->credential->getUserId() === 'Sander');
	}

	public function testJsonSerialize(){
		// Make sure we are dealing with default test data and no other test has altered it
		$this->setUp();

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