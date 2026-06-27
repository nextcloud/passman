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
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Utility\PermissionEntity;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Db\ShareRequest
 */
class ShareRequestTest extends TestCase {
	private const TEST_DATA = [
		'id' => 233,
		'item_id' => 5,
		'item_guid' => 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'target_user_id' => 'WolFi',
		'target_vault_id' => 1,
		'target_vault_guid' => '0D4C9729-3A5E-49F5-A0EA-55FB77D640D0',
		'shared_key' => 'eyJpdiI6IkllTjNqS3NTMkEvd3BHRnB5MjZwMkEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJtdG5CT2ZOL3hlRSIsImN0IjoieVo1S3hCUEJUSm0wZEo2VHFXNGZiOUxtc2lXb29BIn0=',
		'permissions' => 0x07,
		'created' => 1475854509,
		'from_user_id' => 'Sander',
	];

	protected ShareRequest $request;

	protected function setUp(): void {
		parent::setUp();
		$this->request = ShareRequest::fromRow(self::TEST_DATA);
	}

	/**
	 * @covers ::fromRow
	 * @covers ::getter
	 */
	public function testGetters(): void {
		$this->assertSame(self::TEST_DATA['id'], $this->request->getId());
		$this->assertSame(self::TEST_DATA['item_id'], $this->request->getItemId());
		$this->assertSame(self::TEST_DATA['item_guid'], $this->request->getItemGuid());
		$this->assertSame(self::TEST_DATA['target_user_id'], $this->request->getTargetUserId());
		$this->assertSame(self::TEST_DATA['target_vault_id'], $this->request->getTargetVaultId());
		$this->assertSame(self::TEST_DATA['target_vault_guid'], $this->request->getTargetVaultGuid());
		$this->assertSame(self::TEST_DATA['shared_key'], $this->request->getSharedKey());
		$this->assertSame(self::TEST_DATA['permissions'], $this->request->getPermissions());
		$this->assertSame(self::TEST_DATA['created'], $this->request->getCreated());
		$this->assertSame(self::TEST_DATA['from_user_id'], $this->request->getFromUserId());
	}

	/** @covers ::setter */
	public function testSetters(): void {
		$this->request->setSharedKey('ASDF THIS IS A KEY');
		$this->assertSame('ASDF THIS IS A KEY', $this->request->getSharedKey());
	}

	/** @coversNothing */
	public function testPermissionSystemIsWorking(): void {
		$this->assertTrue($this->request->hasPermission(ShareRequest::FILES));
		$this->assertFalse($this->request->hasPermission(ShareRequest::OWNER));
	}

	/** @coversNothing */
	public function testInheritsExpectedClasses(): void {
		$this->assertInstanceOf(PermissionEntity::class, $this->request);
		$this->assertInstanceOf(JsonSerializable::class, $this->request);
	}

	/** @covers ::jsonSerialize */
	public function testJsonSerialize(): void {
		$expected = [
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

		$this->assertSame($expected, $this->request->jsonSerialize());
	}

	/** @covers ::asACLJson */
	public function testAsACLJson(): void {
		$expected = [
			'item_id' => self::TEST_DATA['item_id'],
			'item_guid' => self::TEST_DATA['item_guid'],
			'user_id' => self::TEST_DATA['target_user_id'],
			'created' => self::TEST_DATA['created'],
			'permissions' => self::TEST_DATA['permissions'],
			'vault_id' => self::TEST_DATA['target_vault_id'],
			'vault_guid' => self::TEST_DATA['target_vault_guid'],
			'shared_key' => self::TEST_DATA['shared_key'],
			'pending' => true,
		];

		$this->assertSame($expected, $this->request->asACLJson());
	}
}
