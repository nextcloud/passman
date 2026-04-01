<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

namespace OCA\Passman\Db;


use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<SharingACL>
 */
class SharingACLMapper extends QBMapper {
	const TABLE_NAME = 'passman_sharing_acl';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'passman_sharing_acl');
	}

	/**
	 * @param SharingACL $sharingACL
	 * @return SharingACL
	 */
	public function createACLEntry(SharingACL $sharingACL): SharingACL {
		return $this->insert($sharingACL);
	}

	/**
	 * Gets the currently accepted share requests from the given user for the given vault guid
	 *
	 * @param string $user_id
	 * @param string $vault_guid
	 * @return SharingACL[]
	 */
	public function getVaultEntries(string $user_id, string $vault_guid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('vault_guid', $qb->createNamedParameter($vault_guid, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * Gets the acl for a given item guid
	 *
	 * @param string|null $user_id
	 * @param string $item_guid
	 * @return SharingACL
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getItemACL(?string $user_id, string $item_guid): SharingACL {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)));

		if ($user_id === null) {
			$qb->andWhere($qb->expr()->isNull('user_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
	}

	/**
	 * Update an acl
	 *
	 * @param SharingACL $sharingACL
	 * @return SharingACL
	 */
	public function updateCredentialACL(SharingACL $sharingACL): SharingACL {
		return $this->update($sharingACL);
	}

	/**
	 * Gets the currently accepted share requests from the given user for the given vault guid
	 *
	 * @param string $item_guid
	 * @return SharingACL[]
	 */
	public function getCredentialAclList(string $item_guid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('item_guid', $qb->createNamedParameter($item_guid, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}

	/**
	 * @param SharingACL $sharingACL
	 * @return SharingACL
	 */
	public function deleteShareACL(SharingACL $sharingACL): SharingACL {
		return $this->delete($sharingACL);
	}
}
