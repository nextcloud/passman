<?php
/**
 * Test case for the PermissionEntity class
 * Date: 8/10/16
 * Time: 16:33
 * @copyright Marcos Zuriaga Miguel 2016
 * @license AGPLv3
 */
use \OCA\Passman\Utility\PermissionEntity;

class PermissionsEntityTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var PermissionEntity
	 */
	protected $permission;

	public function setUp() {
		$this->permission = new PermissionEntity();
		$this->permission->permissions = 0;
	}

	public function testPermissionConstants(){
		$this->assertTrue(PermissionEntity::READ    === 0b00000001);
		$this->assertTrue(PermissionEntity::WRITE   === 0b00000010);
		$this->assertTrue(PermissionEntity::FILES   === 0b00000100);
		$this->assertTrue(PermissionEntity::HISTORY === 0b00001000);
		$this->assertTrue(PermissionEntity::OWNER   === 0b10000000);
	}

	public function testAddPermission(){
		// Start with an empty permission
		$this->permission->setPermissions(0);
		$this->assertTrue($this->permission->getPermissions() === 0);

		// Add read permission
		$this->permission->addPermission(PermissionEntity::READ);
		$this->assertTrue($this->permission->getPermissions() === PermissionEntity::READ);

		// Try adding another permission and check if it has both
		$this->permission->addPermission(PermissionEntity::FILES);
		$this->assertTrue($this->permission->getPermissions() === (PermissionEntity::READ | PermissionEntity::FILES));
	}

	public function testRemovePermission(){
		$base_permissions = PermissionEntity::READ | PermissionEntity::WRITE | PermissionEntity::HISTORY;

		// Start with a set of permissions
		$this->permission->setPermissions($base_permissions);
		$this->assertTrue($this->permission->getPermissions() === $base_permissions);

		// Remove write permission
		$expected_permissions = PermissionEntity::READ | PermissionEntity::HISTORY;
		$this->permission->removePermission(PermissionEntity::WRITE);
		$this->assertTrue($this->permission->getPermissions() === $expected_permissions);

		// Remove history permission
		$expected_permissions = PermissionEntity::READ;
		$this->permission->removePermission(PermissionEntity::HISTORY);
		$this->assertTrue($this->permission->getPermissions() === $expected_permissions);
	}

	public function testHasPermission() {
		$base_permissions = PermissionEntity::READ | PermissionEntity::WRITE | PermissionEntity::HISTORY;

		// Start with a set of permissions
		$this->permission->setPermissions($base_permissions);
		$this->assertTrue($this->permission->getPermissions() === $base_permissions);

		// Test some conditions
		$this->assertFalse($this->permission->hasPermission(PermissionEntity::OWNER));
		$this->assertTrue($this->permission->hasPermission(PermissionEntity::READ));
		$this->assertTrue($this->permission->hasPermission(PermissionEntity::HISTORY));
	}
}