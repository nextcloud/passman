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
use OCA\Passman\Command\PassmanBackupCommand;
use OCA\Passman\Service\BackupService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

#[CoversClass(PassmanBackupCommand::class)]
class PassmanBackupCommandTest extends TestCase {
	private BackupService&MockObject $backupService;
	private CommandTester            $tester;

	protected function setUp(): void {
		parent::setUp();

		$this->backupService = $this->createMock(BackupService::class);
		$this->tester = new CommandTester(new PassmanBackupCommand(
			$this->backupService,
			$this->createStub(BackupSerializer::class),
		));
	}

	public function testUserOptionWithoutUserScopeExitsWithFailure(): void {
		$this->backupService->expects($this->never())->method('createBackup');

		$status = $this->tester->execute(['--user' => 'alice']);

		$this->assertSame(Command::FAILURE, $status);
		$this->assertStringContainsString('--user is only supported with --scope=user', $this->tester->getDisplay());
	}
}
