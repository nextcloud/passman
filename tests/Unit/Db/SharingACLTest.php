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

use JsonSerializable;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Utility\PermissionEntity;
use Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\OCA\Passman\Db\SharingACL::class)]
class SharingACLTest extends TestCase {
	private const TEST_DATA = [
		'id' => 55,
		'item_id' => 5,
		'item_guid' => 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'user_id' => 'WolFi',
		'created' => 1475854509,
		'expire' => 0,
		'expire_views' => 0,
		'permissions' => 0x07,
		'vault_id' => 1,
		'vault_guid' => 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'shared_key' => 'eyJpdiI6IkllTjNqS3NTMkEvd3BHRnB5MjZwMkEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJtdG5CT2ZOL3hlRSIsImN0IjoieVo1S3hCUEJUSm0wZEo2VHFXNGZiOUxtc2lXb29BIn0=',
	];

	protected SharingACL $acl;

	protected function setUp(): void {
		parent::setUp();
		$this->acl = SharingACL::fromRow(self::TEST_DATA);
	}

	/**
	 * @covers ::fromRow
	 * @covers ::getter
	 */
	public function testGetters(): void {
		$this->assertEquals(self::TEST_DATA['id'], $this->acl->getId());
		$this->assertEquals(self::TEST_DATA['item_id'], $this->acl->getItemId());
		$this->assertEquals(self::TEST_DATA['item_guid'], $this->acl->getItemGuid());
		$this->assertEquals(self::TEST_DATA['user_id'], $this->acl->getUserId());
		$this->assertEquals(self::TEST_DATA['created'], $this->acl->getCreated());
		$this->assertEquals(self::TEST_DATA['expire'], $this->acl->getExpire());
		$this->assertEquals(self::TEST_DATA['expire_views'], $this->acl->getExpireViews());
		$this->assertEquals(self::TEST_DATA['permissions'], $this->acl->getPermissions());
		$this->assertEquals(self::TEST_DATA['vault_id'], $this->acl->getVaultId());
		$this->assertEquals(self::TEST_DATA['vault_guid'], $this->acl->getVaultGuid());
		$this->assertEquals(self::TEST_DATA['shared_key'], $this->acl->getSharedKey());
	}

	public function testSetters(): void {
		$this->acl->setSharedKey('ASDF THIS IS A KEY');
		$this->assertEquals('ASDF THIS IS A KEY', $this->acl->getSharedKey());
	}

	public function testInheritedExpectedClasses(): void {
		$this->assertInstanceOf(PermissionEntity::class, $this->acl);
		$this->assertInstanceOf(JsonSerializable::class, $this->acl);
	}

	public function testPermissionSystemIsWorking(): void {
		$this->assertTrue($this->acl->hasPermission(PermissionEntity::FILES));
		$this->assertFalse($this->acl->hasPermission(PermissionEntity::OWNER));
	}

	public function testJsonSerialize(): void {
		$expected = [
			'acl_id' => self::TEST_DATA['id'],
			'item_id' => self::TEST_DATA['item_id'],
			'item_guid' => self::TEST_DATA['item_guid'],
			'user_id' => self::TEST_DATA['user_id'],
			'created' => self::TEST_DATA['created'],
			'expire' => self::TEST_DATA['expire'],
			'expire_views' => self::TEST_DATA['expire_views'],
			'permissions' => self::TEST_DATA['permissions'],
			'vault_id' => self::TEST_DATA['vault_id'],
			'vault_guid' => self::TEST_DATA['vault_guid'],
			'shared_key' => self::TEST_DATA['shared_key'],
			'pending' => false,
		];

		$this->assertEquals($expected, $this->acl->jsonSerialize());
	}
}
