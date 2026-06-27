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
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\EntityJSONSerializer;
use OCP\AppFramework\Db\Entity;
use Test\TestCase;

/**
 * @coversDefaultClass \OCA\Passman\Db\Credential
 * @uses \OCA\Passman\Db\EntityJSONSerializer
 * @uses \OCP\AppFramework\Db\Entity
 * @uses \JsonSerializable
 */
class CredentialTest extends TestCase {
	private const TEST_DATA = [
		'id' => 5,
		'guid' => 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'vault_id' => 1,
		'user_id' => 'WolFi',
		'label' => 'Test credential for unit testing',
		'description' => 'eyJpdiI6InhoSVczaEpvUVpoMmo0TWpibkQ1ZEEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiSjB0YkpBUjdMY0pSUGFEaSJ9',
		'created' => 1475963590,
		'changed' => 1475963600,
		'tags' => 'eyJpdiI6InZ5NjUwbEtvejNPa09TOWZuWEs4OVEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoibXlqQnNBSnFWbFVrSlEifQ==',
		'email' => 'eyJpdiI6ImZCblR4S2FuSFBiNGlXWmYxZlBTcmciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiQ0lTSWRNSnE2QzVIbG8zdiJ9',
		'username' => 'eyJpdiI6IlVGUStYNkZTUEYwU3pvd2Z1NUxFcVEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoidFhGTGcrU3RML0Y2dUE4dSJ9',
		'password' => 'eyJpdiI6Ik5Bakc0bVY5SEtBbW5tb1AwTEhKZFEiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiNEhyWTJHUGtkTEs1V0tuYXFtUXBjWXZvSkFqSyJ9',
		'url' => 'eyJpdiI6IkFVZ0RyWFA4S1lwVHl6WGZkK1U3RWciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiQ2NiUUtHQlRmbkYveU5BMyJ9',
		'icon' => 'null',
		'renew_interval' => null,
		'expire_time' => 0,
		'delete_time' => 0,
		'files' => 'eyJpdiI6Ink4UkorSWdSYmthZGdUVEoyKzArT1EiLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiNXpidHJJaE5kSCs4MHcifQ==',
		'custom_fields' => 'eyJpdiI6InJCdlNNNmhINHM4OG9NUld1eTNaTHciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiZDJaRUFiSWZ5a1FJRXcifQ==',
		'otp' => 'eyJpdiI6IjBzL0g0YUZvWVRaN2RFMlhETS9aMnciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiI3ZzdHMDhzN3FIWSIsImN0IjoiMGsvckljcGJwMWU1SVEifQ==',
		'hidden' => 1,
		'shared_key' => null,
		'compromised' => null,
	];

	protected Credential $credential;

	protected function setUp(): void {
		parent::setUp();
		$this->credential = Credential::fromRow(self::TEST_DATA);
	}

	public function testInstances(): void {
		$this->assertInstanceOf(Entity::class, $this->credential);
		$this->assertInstanceOf(JsonSerializable::class, $this->credential);
	}

	public function testGetters(): void {
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
		$this->assertEquals(self::TEST_DATA['icon'], $this->credential->getIcon());
		$this->assertEquals(self::TEST_DATA['renew_interval'], $this->credential->getRenewInterval());
		$this->assertEquals(self::TEST_DATA['expire_time'], $this->credential->getExpireTime());
		$this->assertEquals(self::TEST_DATA['delete_time'], $this->credential->getDeleteTime());
		$this->assertEquals(self::TEST_DATA['files'], $this->credential->getFiles());
		$this->assertEquals(self::TEST_DATA['custom_fields'], $this->credential->getCustomFields());
		$this->assertEquals(self::TEST_DATA['otp'], $this->credential->getOtp());
		$this->assertEquals(self::TEST_DATA['hidden'], $this->credential->getHidden());
		$this->assertEquals(self::TEST_DATA['shared_key'], $this->credential->getSharedKey());
		$this->assertEquals(self::TEST_DATA['compromised'], $this->credential->getCompromised());
	}

	public function testSetters(): void {
		$this->credential->setUserId('Sander');
		$this->assertSame('Sander', $this->credential->getUserId());
	}

	public function testJsonSerialize(): void {
		$comparisonArray = [
			'credential_id' => self::TEST_DATA['id'],
			'guid' => self::TEST_DATA['guid'],
			'user_id' => self::TEST_DATA['user_id'],
			'vault_id' => self::TEST_DATA['vault_id'],
			'label' => self::TEST_DATA['label'],
			'description' => self::TEST_DATA['description'],
			'created' => self::TEST_DATA['created'],
			'changed' => self::TEST_DATA['changed'],
			'tags' => self::TEST_DATA['tags'],
			'email' => self::TEST_DATA['email'],
			'username' => self::TEST_DATA['username'],
			'password' => self::TEST_DATA['password'],
			'url' => self::TEST_DATA['url'],
			'icon' => null,
			'renew_interval' => self::TEST_DATA['renew_interval'],
			'expire_time' => self::TEST_DATA['expire_time'],
			'delete_time' => self::TEST_DATA['delete_time'],
			'files' => self::TEST_DATA['files'],
			'custom_fields' => self::TEST_DATA['custom_fields'],
			'otp' => self::TEST_DATA['otp'],
			'hidden' => self::TEST_DATA['hidden'],
			'shared_key' => self::TEST_DATA['shared_key'],
			'compromised' => self::TEST_DATA['compromised'],
		];

		$this->assertEquals($comparisonArray, $this->credential->jsonSerialize());
	}
}
