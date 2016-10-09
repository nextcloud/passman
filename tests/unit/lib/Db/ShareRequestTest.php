<?php

/**
 * Test case for the Share request model class
 * Date: 9/10/16
 * Time: 14:18
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
 */
use \OCA\Passman\Db\ShareRequest;
use \OCA\Passman\Utility\PermissionEntity;

class ShareRequestTest extends PHPUnit_Framework_TestCase {
	CONST TEST_DATA = [
		'id'				=> 233,
		'item_id'			=> 5,
		'item_guid'			=> 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'target_user_id'	=> 'WolFi',
		'target_vault_id'	=> 1,
		'target_vault_guid'	=> '0D4C9729-3A5E-49F5-A0EA-55FB77D640D0',
		'shared_key'		=> 'eyJpdiI6IkllTjNqS3NTMkEvd3BHRnB5MjZwMkEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJtdG5CT2ZOL3hlRSIsImN0IjoieVo1S3hCUEJUSm0wZEo2VHFXNGZiOUxtc2lXb29BIn0=',
		'permissions'		=> 0x07,
		'created'			=> 1475854509,
		'from_user_id'		=> 'Sander'
	];

	/**
	 * @var ShareRequest
	 */
	protected $request;

	public function setUp() {
		$this->request = ShareRequest::fromRow(self::TEST_DATA);
	}

	public function testGetters(){
		$this->assertEquals(self::TEST_DATA['id'], $this->request->getId());
		$this->assertEquals(self::TEST_DATA['item_id'], $this->request->getItemId());
		$this->assertEquals(self::TEST_DATA['item_guid'], $this->request->getItemGuid());
		$this->assertEquals(self::TEST_DATA['target_user_id'], $this->request->getTargetUserId());
		$this->assertEquals(self::TEST_DATA['target_vault_id'], $this->request->getTargetVaultId());
		$this->assertEquals(self::TEST_DATA['target_vault_guid'], $this->request->getTargetVaultGuid());
		$this->assertEquals(self::TEST_DATA['shared_key'], $this->request->getSharedKey());
		$this->assertEquals(self::TEST_DATA['permissions'], $this->request->getPermissions());
		$this->assertEquals(self::TEST_DATA['created'], $this->request->getCreated());
		$this->assertEquals(self::TEST_DATA['from_user_id'], $this->request->getFromUserId());
	}

	public function testSetters(){
		/**
		 * Only testing one setter since if it works all setters should work because php magic.
		 * please, if you override a setter implement it here.
		 */
		$this->request->setSharedKey('ASDF THIS IS A KEY');
		$this->assertEquals('ASDF THIS IS A KEY', $this->request->getSharedKey());
	}

	public function testPermissionSystemIsWorking(){
		$this->assertTrue($this->request->hasPermission(ShareRequest::FILES));
		$this->assertFalse($this->request->hasPermission(ShareRequest::OWNER));
	}

	public function testInheritsExpectedClasses(){
		$this->assertInstanceOf(PermissionEntity::class, $this->request);
		$this->assertInstanceOf(\JsonSerializable::class, $this->request);
	}

	public function testJsonSerialize(){
		// Ensure we have clean test data
		$this->setUp();

		$expected_result = [
			'req_id' => self::TEST_DATA['id'],
			'item_id' => self::TEST_DATA['item_id'],
			'item_guid' => self::TEST_DATA['item_guid'],
			'target_user_id' => self::TEST_DATA['target_user_id'],
			'target_vault_id' => self::TEST_DATA['target_vault_id'],
			'target_vault_guid' => self::TEST_DATA['target_vault_guid'],
			'from_user_id' => self::TEST_DATA['from_user_id'],
			'shared_key' => self::TEST_DATA['shared_key'],
			'permissions' => self::TEST_DATA['permissions'],
			'created' => self::TEST_DATA['created'],
		];

		$actual_data = $this->request->jsonSerialize();

		$this->assertEquals($expected_result, $actual_data);
	}

	public function testAsACLJson() {
		// Ensure we have clean test data
		$this->setUp();

		$expected_result = [
			'item_id' => self::TEST_DATA['item_id'],
			'item_guid' => self::TEST_DATA['item_guid'],
			'user_id' => self::TEST_DATA['target_user_id'],
			'created' => self::TEST_DATA['created'],
			'permissions' => self::TEST_DATA['permissions'],
			'vault_id' => self::TEST_DATA['target_vault_id'],
			'vault_guid' => self::TEST_DATA['target_vault_guid'],
			'shared_key' => self::TEST_DATA['shared_key'],
			'pending'   => true,
		];

		$actual_data = $this->request->asACLJson();

		$this->assertEquals($expected_result, $actual_data);
	}
}