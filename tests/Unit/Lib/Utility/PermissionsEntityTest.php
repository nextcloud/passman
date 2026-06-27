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

use OCA\Passman\Utility\PermissionEntity;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Utility\PermissionEntity
 */
class PermissionsEntityTest extends TestCase {
	protected PermissionEntity $permission;

	protected function setUp(): void {
		parent::setUp();
		$this->permission = new class extends PermissionEntity {
			protected $permissions;

			public function __construct() {
				$this->addType('permissions', 'integer');
				$this->setPermissions(0);
			}
		};
	}

	/** @coversNothing */
	public function testPermissionConstants(): void {
		$this->assertEquals(0b00000001, PermissionEntity::READ);
		$this->assertEquals(0b00000010, PermissionEntity::WRITE);
		$this->assertEquals(0b00000100, PermissionEntity::FILES);
		$this->assertEquals(0b00001000, PermissionEntity::HISTORY);
		$this->assertEquals(0b10000000, PermissionEntity::OWNER);
	}

	/** @covers ::addPermission */
	public function testAddPermission(): void {
		$this->permission->setPermissions(0);
		$this->assertEquals(0, $this->permission->getPermissions());

		$this->permission->addPermission(PermissionEntity::READ);
		$this->assertEquals(PermissionEntity::READ, $this->permission->getPermissions());

		$this->permission->addPermission(PermissionEntity::FILES);
		$this->assertEquals(PermissionEntity::READ | PermissionEntity::FILES, $this->permission->getPermissions());
	}

	/** @covers ::removePermission */
	public function testRemovePermission(): void {
		$basePermissions = PermissionEntity::READ | PermissionEntity::WRITE | PermissionEntity::HISTORY;

		$this->permission->setPermissions($basePermissions);
		$this->assertEquals($basePermissions, $this->permission->getPermissions());

		$expectedPermissions = PermissionEntity::READ | PermissionEntity::HISTORY;
		$this->permission->removePermission(PermissionEntity::WRITE);
		$this->assertEquals($expectedPermissions, $this->permission->getPermissions());
		$this->assertNotEquals($basePermissions, $this->permission->getPermissions());

		$expectedPermissions = PermissionEntity::READ;
		$this->permission->removePermission(PermissionEntity::HISTORY);
		$this->assertEquals($expectedPermissions, $this->permission->getPermissions());
	}

	/** @covers ::hasPermission */
	public function testHasPermission(): void {
		$basePermissions = PermissionEntity::READ | PermissionEntity::WRITE | PermissionEntity::HISTORY;

		$this->permission->setPermissions($basePermissions);
		$this->assertTrue($this->permission->getPermissions() === $basePermissions);

		$this->assertFalse($this->permission->hasPermission(PermissionEntity::OWNER));
		$this->assertTrue($this->permission->hasPermission(PermissionEntity::READ));
		$this->assertTrue($this->permission->hasPermission(PermissionEntity::HISTORY));
	}
}
