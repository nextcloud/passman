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

namespace OCA\Passman\Command;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\BackupSerializer;
use OCA\Passman\Service\BackupService;
use OCP\AppFramework\Db\DoesNotExistException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes a Passman backup artifact.
 *
 * The command asks no questions, so it stays usable from cron.
 * Without --output the artifact goes to stdout and every message to stderr, which keeps piping the artifact intact.
 */
class PassmanBackupCommand extends AbstractInteractiveCommand {

	public function __construct(
		private readonly BackupService $backupService,
		private readonly BackupSerializer $serializer,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('passman:backup')
			->setDescription(
				'Export passman data into a (JSON) backup artifact.' . PHP_EOL
				. '  Without --output the artifact goes to stdout and every message to stderr, which keeps piping the artifact intact.'
			)
			->addOption(
				'scope',
				null,
				InputOption::VALUE_REQUIRED,
				'What to export: "' . implode('", "', BackupManifest::SCOPES) . '". "' . BackupManifest::SCOPE_INSTANCE . '" is recommended, '
				. 'the vault scopes cannot export file attachments',
				BackupManifest::SCOPE_INSTANCE
			)
			->addOption('user', null, InputOption::VALUE_REQUIRED, 'The user id to export, required for the "' . BackupManifest::SCOPE_USER . '" scope')
			->addOption(
				'vault',
				null,
				InputOption::VALUE_REQUIRED,
				'The vault guid to export, required for the "' . BackupManifest::SCOPE_VAULT . '" scope (can\'t include file attachments)'
			)
			->addOption(
				'encryption',
				null,
				InputOption::VALUE_REQUIRED,
				'How to handle the nextcloud server side encryption:' . PHP_EOL
				. '- "' . BackupManifest::MODE_PORTABLE . '" strips it so the artifact can be restored on any instance,' . PHP_EOL
				. '- "' . BackupManifest::MODE_RAW . '" keeps every protected column verbatim (restorable on this instance only).' . PHP_EOL
				. 'The encryption is bound to the encryption settings (like secret and passwordsalt) of this Nextcloud instance config.php',
				BackupManifest::MODE_PORTABLE
			)
			->addOption(
				'output',
				null,
				InputOption::VALUE_REQUIRED,
				'File to write the artifact to, defaults to stdout'
			)
			->addOption('no-summary', null, InputOption::VALUE_NONE, 'Don\'t print the summary to stderr');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$messages = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

		$scope = (string)$input->getOption('scope');
		$encryptionMode = (string)$input->getOption('encryption');
		$userId = $this->readStringOption($input, 'user');
		$vaultGuid = $this->readStringOption($input, 'vault');
		$target = $this->readStringOption($input, 'output');
		$noSummary = (bool)$input->getOption('no-summary');

		if ($userId !== null && $scope !== BackupManifest::SCOPE_USER) {
			$messages->writeln('<error>--user is only supported with --scope=' . BackupManifest::SCOPE_USER . '</error>');
			return self::FAILURE;
		}
		if ($vaultGuid !== null && $scope !== BackupManifest::SCOPE_VAULT) {
			$messages->writeln('<error>--vault is only supported with --scope=' . BackupManifest::SCOPE_VAULT . '</error>');
			return self::FAILURE;
		}

		try {
			$archive = $this->backupService->createBackup($scope, $encryptionMode, $userId, $vaultGuid);
			$artifact = $this->serializer->encode($archive);
		} catch (DoesNotExistException) {
			$messages->writeln('<error>The vault "' . $vaultGuid . '" does not exist</error>');
			return self::FAILURE;
		} catch (\InvalidArgumentException|\RuntimeException $e) {
			$messages->writeln('<error>' . $e->getMessage() . '</error>');
			return self::FAILURE;
		}

		if ($target === null) {
			$output->writeln($artifact, OutputInterface::OUTPUT_RAW);
		} elseif (file_put_contents($target, $artifact) === false) {
			$messages->writeln('<error>Could not write the backup artifact to "' . $target . '"</error>');
			return self::FAILURE;
		}

		if (!$noSummary) {
			$this->printSummary($messages, $archive, $target);
		}
		return self::SUCCESS;
	}

	private function printSummary(OutputInterface $messages, BackupArchive $archive, ?string $target): void {
		$manifest = $archive->manifest;

		$scope = $manifest->scope;
		if ($manifest->targetUserId !== null) {
			$scope .= ' "' . $manifest->targetUserId . '"';
		}
		if ($manifest->targetVaultGuid !== null) {
			$scope .= ' "' . $manifest->targetVaultGuid . '"';
		}

		$messages->writeln('Created a ' . $manifest->encryptionMode . ' backup of scope ' . $scope . ':');
		foreach ($manifest->counts as $section => $count) {
			$messages->writeln(sprintf('  %-22s %d', $section, $count));
		}

		$messages->writeln('Checksum (' . BackupManifest::CHECKSUM_ALGORITHM . '): ' . $manifest->checksum);

		if ($target !== null) {
			$messages->writeln('Written to ' . $target);
		}

		foreach ($this->backupService->getCaveats($manifest->scope, $manifest->encryptionMode) as $caveat) {
			$messages->writeln('<comment>Note: ' . $caveat . '</comment>');
		}
	}

	private function readStringOption(InputInterface $input, string $name): ?string {
		$value = $input->getOption($name);
		if ($value === null || $value === '') {
			return null;
		}
		return (string)$value;
	}
}
