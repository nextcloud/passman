<?php
/**
 * Nextcloud - passman
 *
 * @copyright Marius David Wieschollek, file was part of the Passwords App
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

namespace OCA\Passman\Command;

use OCA\Passman\Exception\NonInteractiveShellException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class AbstractInteractiveCommand
 *
 * @package OCA\Passwords\Command
 */
abstract class AbstractInteractiveCommand extends Command
{

	/**
	 * AbstractInteractiveCommand constructor.
	 *
	 * @param string|null $name
	 */
	public function __construct(?string $name = null) {
		parent::__construct($name);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws NonInteractiveShellException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$input->isInteractive() && !$input->getOption('no-interaction')) {
			throw new NonInteractiveShellException();
		} elseif (!$input->isInteractive()) {
			$output->writeln('"--no-interaction" is set, will assume yes for all questions.');
			$output->writeln('');
		}

		return 0;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param string $description
	 *
	 * @return bool
	 */
	protected function requestConfirmation(InputInterface $input, OutputInterface $output, string $description): bool {
		$output->writeln("❗❗❗ {$description} ❗❗❗");
		if (!$input->isInteractive()) {
			$output->writeln('');
			return true;
		}

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');
		$question = new Question('Type "yes" to confirm this: ');
		$yes = $helper->ask($input, $output, $question);
		$output->writeln('');

		if ($yes !== 'yes') {
			$output->writeln('aborting');

			return false;
		}

		return true;
	}
}
