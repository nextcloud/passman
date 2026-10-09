<?php
/**
 * Nextcloud - passman
 *
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

namespace OCA\Passman\Tests\Unit\Command;

use OCA\Passman\Command\CheckSharingAcls;
use OCA\Passman\Db\SharingACLMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

#[CoversClass(CheckSharingAcls::class)]
class CheckSharingAclsTest extends TestCase {
	private SharingACLMapper&MockObject $mapper;
	private CommandTester               $tester;

	protected function setUp(): void {
		parent::setUp();

		$this->mapper = $this->createMock(SharingACLMapper::class);
		$this->tester = new CommandTester(new CheckSharingAcls($this->mapper));
	}

	private function row(array $overrides = []): array {
		return array_merge([
			'id'                         => 3,
			'item_id'                    => 42,
			'item_guid'                  => 'own-guid',
			'user_id'                    => null,
			'created'                    => 0,
			'expire'                     => 0,
			'expire_views'               => 5,
			'permissions'                => 1,
			'item_id_credential_guid'    => 'victim-guid',
			'item_id_credential_owner'   => 'victim',
			'item_guid_credential_id'    => 7,
			'item_guid_credential_owner' => 'attacker',
		], $overrides);
	}

	public function testReportsNoEntries(): void {
		$this->mapper->method('findEntriesWithMismatchingItemId')->willReturn([]);

		$this->assertSame(0, $this->tester->execute([]));
		$this->assertStringContainsString('No sharing ACL entries with mismatching item_id found.', $this->tester->getDisplay());
	}

	public function testListsMismatchingEntriesAsTable(): void {
		$this->mapper->method('findEntriesWithMismatchingItemId')->willReturn([$this->row()]);

		$this->assertSame(0, $this->tester->execute([]));
		$display = $this->tester->getDisplay();
		$this->assertStringContainsString('public link', $display);
		$this->assertStringContainsString('victim', $display);
		$this->assertStringContainsString('attacker', $display);
		$this->assertStringContainsString(CheckSharingAcls::REASON_FOREIGN_CREDENTIAL, $display);
		$this->assertStringContainsString('Found 1 sharing ACL entries', $display);
	}

	public function testListsMismatchingEntriesAsJson(): void {
		$this->mapper->method('findEntriesWithMismatchingItemId')->willReturn([
			$this->row(),
			$this->row([
				'id'                       => 4,
				'user_id'                  => 'alice',
				'created'                  => 1760000000,
				'item_id_credential_guid'  => null,
				'item_id_credential_owner' => null,
			]),
		]);

		$this->assertSame(0, $this->tester->execute(['--json' => true]));
		$entries = json_decode($this->tester->getDisplay(), true);

		$this->assertCount(2, $entries);
		$this->assertSame('public link', $entries[0]['shared_with']);
		$this->assertNull($entries[0]['created']);
		$this->assertSame('victim', $entries[0]['item_id_credential_owner']);
		$this->assertSame(CheckSharingAcls::REASON_FOREIGN_CREDENTIAL, $entries[0]['reason']);
		$this->assertSame('alice', $entries[1]['shared_with']);
		$this->assertSame(date('Y-m-d H:i:s', 1760000000), $entries[1]['created']);
		$this->assertSame(CheckSharingAcls::REASON_MISSING_CREDENTIAL, $entries[1]['reason']);
	}
}
