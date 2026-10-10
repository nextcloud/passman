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

use OCA\Passman\Db\SharingACLMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists sharing acl entries whose item_id does not belong to the credential of their item_guid.
 * Passman <= 2.6.3 did not validate these values, which allowed accessing foreign credentials by their item_id.
 */
class CheckSharingAcls extends Command {
	public const REASON_FOREIGN_CREDENTIAL = 'item_id references a different credential';
	public const REASON_MISSING_CREDENTIAL = 'item_id references no existing credential';
	public const REASON_NO_SHARED_KEY = 'item_guid credential has no shared key';

	public function __construct(
		private readonly SharingACLMapper $sharingACLMapper,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('passman:sharing:check-acls')
			->setDescription('List sharing ACL entries with mismatching item_id and item_guid or without a shared key of the shared credential')
			->addOption('json', null, InputOption::VALUE_NONE, 'Output the result as JSON');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$entries = array_map($this->formatEntry(...), $this->sharingACLMapper->findInconsistentEntries());

		if ($input->getOption('json')) {
			$output->writeln(json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return self::SUCCESS;
		}

		if (empty($entries)) {
			$output->writeln('<info>No inconsistent sharing ACL entries found.</info>');
			return self::SUCCESS;
		}

		$table = new Table($output);
		$table->setHeaders([
			'ACL id', 'Shared with', 'Created', 'item_guid', 'item_guid owner', 'item_guid shared key', 'item_id', 'item_id owner', 'Reason',
		]);
		foreach ($entries as $entry) {
			$table->addRow([
				$entry['acl_id'],
				$entry['shared_with'],
				$entry['created'],
				$entry['item_guid'],
				$entry['item_guid_credential_owner'] ?? '-',
				match ($entry['item_guid_credential_has_shared_key']) {
					true => 'yes',
					false => 'no',
					null => '-',
				},
				$entry['item_id'],
				$entry['item_id_credential_owner'] ?? '-',
				$entry['reason'],
			]);
		}
		$table->render();

		$output->writeln(sprintf('<comment>Found %d inconsistent sharing ACL entries.</comment>', count($entries)));
		$output->writeln('Entries referencing a different credential may be the result of an attempt to access foreign credentials,');
		$output->writeln('the "item_id owner" is the user whose credential was exposed. Passman ignores such entries since the fix.');
		$output->writeln('Every share (link or user) requires the credential to have a shared key. Without one ("item_guid shared key" = no),');
		$output->writeln('the entry cannot belong to a legitimate share of the item_guid owner, even if item_id and item_guid match.');
		return self::SUCCESS;
	}

	private function formatEntry(array $row): array {
		$created = (int)$row['created'];
		return [
			'acl_id'                              => (int)$row['id'],
			'shared_with'                         => $row['user_id'] ?? 'public link',
			'created'                             => $created > 0 ? date('Y-m-d H:i:s', $created) : null,
			'permissions'                         => (int)$row['permissions'],
			'expire'                              => (int)$row['expire'],
			'expire_views'                        => (int)$row['expire_views'],
			'item_guid'                           => $row['item_guid'],
			'item_guid_credential_id'             => $row['item_guid_credential_id'] !== null ? (int)$row['item_guid_credential_id'] : null,
			'item_guid_credential_owner'          => $row['item_guid_credential_owner'],
			'item_guid_credential_has_shared_key' => $row['item_guid_credential_has_shared_key'] !== null ? (bool)$row['item_guid_credential_has_shared_key'] : null,
			'item_id'                             => (int)$row['item_id'],
			'item_id_credential_guid'             => $row['item_id_credential_guid'],
			'item_id_credential_owner'            => $row['item_id_credential_owner'],
			'reason'                              => $this->getReason($row),
		];
	}

	private function getReason(array $row): string {
		if ($row['item_id_credential_guid'] === null) {
			return self::REASON_MISSING_CREDENTIAL;
		}
		if ($row['item_id_credential_guid'] !== $row['item_guid']) {
			return self::REASON_FOREIGN_CREDENTIAL;
		}
		return self::REASON_NO_SHARED_KEY;
	}
}
