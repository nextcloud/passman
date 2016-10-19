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

use \OCA\Passman\Utility\PermissionEntity;

/**
 * @coversDefaultClass \OCA\Passman\Utility\PermissionEntity
 */
class PermissionsEntityTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var PermissionEntity
	 */
	protected $permission;

	/**
	 * @after
	 */
	public function setUp() {
		$this->permission = new PermissionEntity();
		$this->permission->permissions = 0;
	}

	/**
	 * @coversNothing
	 */
	public function testPermissionConstants(){
		$this->assertEquals(0b00000001, PermissionEntity::READ);
		$this->assertEquals(0b00000010, PermissionEntity::WRITE);
		$this->assertEquals(0b00000100, PermissionEntity::FILES);
		$this->assertEquals(0b00001000, PermissionEntity::HISTORY);
		$this->assertEquals(0b10000000, PermissionEntity::OWNER);
	}

	/**
	 * @covers ::addPermission
	 */
	public function testAddPermission(){
		// Start with an empty permission
		$this->permission->setPermissions(0);
		$this->assertEquals(0, $this->permission->getPermissions());

		// Add read permission
		$this->permission->addPermission(PermissionEntity::READ);
		$this->assertEquals(PermissionEntity::READ, $this->permission->getPermissions());

		// Try adding another permission and check if it has both
		$this->permission->addPermission(PermissionEntity::FILES);
		$this->assertEquals(PermissionEntity::READ | PermissionEntity::FILES, $this->permission->getPermissions());
	}

	/**
	 * @covers ::removePermission
	 */
	public function testRemovePermission(){
		$base_permissions = PermissionEntity::READ | PermissionEntity::WRITE | PermissionEntity::HISTORY;

		// Start with a set of permissions
		$this->permission->setPermissions($base_permissions);
		$this->assertEquals($base_permissions, $this->permission->getPermissions());

		// Remove write permission
		$expected_permissions = PermissionEntity::READ | PermissionEntity::HISTORY;
		$this->permission->removePermission(PermissionEntity::WRITE);
		$this->assertEquals($expected_permissions, $this->permission->getPermissions());
		$this->assertNotEquals($base_permissions, $this->permission->getPermissions());

		// Remove history permission
		$expected_permissions = PermissionEntity::READ;
		$this->permission->removePermission(PermissionEntity::HISTORY);
		$this->assertEquals($expected_permissions, $this->permission->getPermissions());
	}

	/**
	 * @covers ::hasPermission
	 */
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