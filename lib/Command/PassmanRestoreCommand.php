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

namespace OCA\Passman\Command;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\BackupSerializer;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Service\RestoreService;
use OCP\DB\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Restores a Passman backup artifact written by {@see PassmanBackupCommand}.
 *
 * The scope and the encryption mode are read from the manifest of the artifact,
 * the caller only decides whether existing data is replaced or merged.
 */
class PassmanRestoreCommand extends AbstractInteractiveCommand {

	public function __construct(
		private readonly RestoreService   $restoreService,
		private readonly BackupSerializer $serializer,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('passman:restore')
			->setDescription(
				'Restore Passman data from a (JSON) backup artifact.' . PHP_EOL
				. '  The scope (instance, user or vault) and the encryption handling are read from the artifact.'
			)
			->addOption('input', null, InputOption::VALUE_REQUIRED, 'The backup artifact to restore')
			->addOption(
				'mode',
				null,
				InputOption::VALUE_REQUIRED,
				'How to apply the artifact:' . PHP_EOL
				. '- "' . RestoreService::MODE_MERGE . '" keeps existing rows and adds or updates the rows of the artifact,' . PHP_EOL
				. '- "' . RestoreService::MODE_REPLACE . '" deletes everything within the scope of the artifact first (destructive).',
				RestoreService::MODE_MERGE
			)
			->addOption(
				'force',
				null,
				InputOption::VALUE_NONE,
				'Restore a "' . BackupManifest::MODE_RAW . '" artifact of another instance anyway. Its server side encrypted '
				. 'columns stay unreadable on this instance'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$source = $this->readStringOption($input, 'input');
		$mode = (string)$input->getOption('mode');
		$force = (bool)$input->getOption('force');

		if ($source === null) {
			$output->writeln('<error>The --input option with the path of a backup artifact is required</error>');
			return self::FAILURE;
		}
		if (!RestoreService::isValidMode($mode)) {
			$output->writeln('<error>Unknown restore mode "' . $mode . '", expected one of "' . implode('", "', RestoreService::MODES) . '"</error>');
			return self::FAILURE;
		}

		$artifact = $this->readArtifact($source, $output);
		if ($artifact === null) {
			return self::FAILURE;
		}

		try {
			$archive = $this->serializer->decode($artifact);
		} catch (\RuntimeException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return self::FAILURE;
		}

		$this->printManifest($output, $archive, $source, $mode);

		if ($mode === RestoreService::MODE_REPLACE) {
			$interactionCheck = parent::execute($input, $output);
			if ($interactionCheck !== self::SUCCESS) {
				return $interactionCheck;
			}
			if (!$this->requestConfirmation($input, $output, $this->describeDeletion($archive->manifest))) {
				return self::FAILURE;
			}
		}

		try {
			$result = $this->restoreService->restore($archive, $mode, $force);
		} catch (\InvalidArgumentException|\RuntimeException|Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return self::FAILURE;
		}

		$this->printSummary($output, $result);
		return self::SUCCESS;
	}

	/**
	 * @return string|null null when the artifact cannot be read
	 */
	private function readArtifact(string $source, OutputInterface $output): ?string {
		if (!is_file($source) || !is_readable($source)) {
			$output->writeln('<error>The backup artifact "' . $source . '" does not exist or cannot be read</error>');
			return null;
		}

		$artifact = file_get_contents($source);
		if ($artifact === false) {
			$output->writeln('<error>Could not read the backup artifact "' . $source . '"</error>');
			return null;
		}

		return $artifact;
	}

	private function printManifest(OutputInterface $output, BackupArchive $archive, string $source, string $mode): void {
		$manifest = $archive->manifest;

		$output->writeln(sprintf(
			'About to %s a %s backup of scope %s, created %s by Passman %s on the instance "%s":',
			$mode,
			$manifest->encryptionMode,
			$this->describeScope($manifest),
			gmdate('Y-m-d H:i:s', $manifest->createdAt) . ' UTC',
			$manifest->appVersion === '' ? '(unknown version)' : $manifest->appVersion,
			$manifest->instanceId
		));
		$output->writeln('  ' . $source . ' (checksum ' . BackupManifest::CHECKSUM_ALGORITHM . ':' . $manifest->checksum . ' verified)');

		foreach (BackupArchive::SECTIONS as $section) {
			$output->writeln(sprintf('  %-22s %d rows', $section, $archive->section($section)->count()));
		}

		foreach ($this->restoreService->getCaveats($manifest, $mode) as $caveat) {
			$output->writeln('<comment>Note: ' . $caveat . '</comment>');
		}
		$output->writeln('');
	}

	private function printSummary(OutputInterface $output, RestoreResult $result): void {
		$output->writeln(sprintf(
			'Restored %d rows (%d inserted, %d updated), deleted %d rows, skipped %d rows:',
			$result->totalInserted() + $result->totalUpdated(),
			$result->totalInserted(),
			$result->totalUpdated(),
			$result->totalDeleted(),
			$result->totalSkipped()
		));
		$output->writeln(sprintf('  %-22s %9s %9s %8s %8s', '', 'deleted', 'inserted', 'updated', 'skipped'));

		foreach (BackupArchive::SECTIONS as $section) {
			$output->writeln(sprintf(
				'  %-22s %9d %9d %8d %8d',
				$section,
				$result->deleted[$section],
				$result->inserted[$section],
				$result->updated[$section],
				$result->skipped[$section]
			));
		}

		foreach ($result->warnings as $warning) {
			$output->writeln('<comment>' . $warning . '</comment>');
		}
	}

	private function describeScope(BackupManifest $manifest): string {
		if ($manifest->targetUserId !== null) {
			return $manifest->scope . ' "' . $manifest->targetUserId . '"';
		}
		if ($manifest->targetVaultGuid !== null) {
			return $manifest->scope . ' "' . $manifest->targetVaultGuid . '"';
		}
		return $manifest->scope;
	}

	private function describeDeletion(BackupManifest $manifest): string {
		return match ($manifest->scope) {
			BackupManifest::SCOPE_USER => 'This deletes every existing Passman vault, credential, file attachment, revision and share of the user "'
				. $manifest->targetUserId . '" on this instance before restoring the backup!',
			BackupManifest::SCOPE_VAULT => 'This deletes the existing vault "' . $manifest->targetVaultGuid
				. '" with its credentials, revisions and shares on this instance before restoring the backup!',
			default => 'This deletes ALL existing Passman data of EVERY user of this instance before restoring the backup!',
		};
	}

	private function readStringOption(InputInterface $input, string $name): ?string {
		$value = $input->getOption($name);
		if ($value === null || $value === '') {
			return null;
		}
		return (string)$value;
	}
}
