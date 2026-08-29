<?php
/**
 * Nextcloud - Passman
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

use OCA\Passman\BackupRestore\BackupSerializer;
use OCA\Passman\Command\PassmanRestoreCommand;
use OCA\Passman\Service\RestoreService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

#[CoversClass(PassmanRestoreCommand::class)]
class PassmanRestoreCommandTest extends TestCase {
	private RestoreService&MockObject   $restoreService;
	private BackupSerializer&MockObject $serializer;
	private CommandTester               $tester;

	protected function setUp(): void {
		parent::setUp();

		$this->restoreService = $this->createMock(RestoreService::class);
		$this->serializer = $this->createMock(BackupSerializer::class);
		$this->tester = new CommandTester(new PassmanRestoreCommand($this->restoreService, $this->serializer));
	}

	public function testMissingInputExitsWithFailure(): void {
		$this->serializer->expects($this->never())->method('decode');
		$this->restoreService->expects($this->never())->method('restore');

		$status = $this->tester->execute([]);

		$this->assertSame(Command::FAILURE, $status);
		$this->assertStringContainsString('--input', $this->tester->getDisplay());
	}

	public function testUnknownModeExitsWithFailure(): void {
		$this->serializer->expects($this->never())->method('decode');

		$this->restoreService->expects($this->never())->method('restore');

		$status = $this->tester->execute([
			'--input' => '/tmp/backup.json',
			'--mode' => 'nope',
		]);

		$this->assertSame(Command::FAILURE, $status);
		$this->assertStringContainsString('Unknown restore mode "nope"', $this->tester->getDisplay());
	}

	public function testUnreadableFileExitsWithFailure(): void {
		$this->serializer->expects($this->never())->method('decode');

		$this->restoreService->expects($this->never())->method('restore');

		$status = $this->tester->execute([
			'--input' => '/no/such/passman-backup.json',
		]);

		$this->assertSame(Command::FAILURE, $status);
		$this->assertStringContainsString('does not exist or cannot be read', $this->tester->getDisplay());
	}
}
