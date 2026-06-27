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

namespace OCA\Passman\Tests\Unit\Support;

use OCA\Passman\Utility\Utils;
use OCP\IDBConnection;

trait DbTestTrait {
	protected function deletePassmanRows(IDBConnection $db, string $table, string $userColumn, string $userId): void {
		$qb = $db->getQueryBuilder();
		$qb->delete($table)
			->where($qb->expr()->eq($userColumn, $qb->createNamedParameter($userId)));
		$qb->executeStatement();
	}

	protected function deleteShareRequestsForUser(IDBConnection $db, string $userId): void {
		$qb = $db->getQueryBuilder();
		$qb->delete('passman_share_request')
			->where($qb->expr()->orX(
				$qb->expr()->eq('from_user_id', $qb->createNamedParameter($userId)),
				$qb->expr()->eq('target_user_id', $qb->createNamedParameter($userId)),
			));
		$qb->executeStatement();
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function sampleCredentialData(int $vaultId, string $userId, array $overrides = []): array {
		return array_merge([
			'vault_id' => $vaultId,
			'user_id' => $userId,
			'label' => 'test label',
			'description' => 'description',
			'created' => 0,
			'changed' => 0,
			'tags' => 'tag1',
			'email' => 'test@example.com',
			'username' => 'user',
			'password' => 'secret',
			'url' => 'https://example.com',
			'icon' => '',
			'renew_interval' => 0,
			'expire_time' => Utils::getTime() + 3600,
			'delete_time' => null,
			'files' => '{}',
			'custom_fields' => '{}',
			'otp' => '',
			'hidden' => false,
			'compromised' => null,
			'shared_key' => null,
		], $overrides);
	}
}
