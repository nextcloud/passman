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

use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class CredentialRevisionMapper extends QBMapper {
	const TABLE_NAME = 'passman_revisions';
	private Utils $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, self::TABLE_NAME);
		$this->utils = $utils;
	}


	/**
	 * Get revisions from a credential
	 *
	 * @param int $credential_id
	 * @param string|null $user_id
	 * @return Entity[]
	 */
	public function getRevisions(int $credential_id, string $user_id = null) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('credential_id', $qb->createNamedParameter($credential_id, IQueryBuilder::PARAM_INT)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntities($qb);
	}

	/**
	 * @param int $revision_id
	 * @param string|null $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getRevision(int $revision_id, string $user_id = null) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($revision_id, IQueryBuilder::PARAM_INT)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
	}

	/**
	 * Create a revision
	 * @param $credential
	 * @param $userId
	 * @param $credential_id
	 * @param $edited_by
	 * @return CredentialRevision
	 */
	public function create($credential, $userId, $credential_id, $edited_by) {
		$revision = new CredentialRevision();
		$revision->setGuid($this->utils->GUID());
		$revision->setUserId($userId);
		$revision->setCreated($this->utils->getTime());
		$revision->setCredentialId($credential_id);
		$revision->setEditedBy($edited_by);
		$revision->setCredentialData(base64_encode(json_encode($credential)));
		return $this->insert($revision);
	}


	/**
	 * Delete a revision
	 * @param $revision_id
	 * @param $user_id
	 * @return CredentialRevision
	 */
	public function deleteRevision($revision_id, $user_id) {
		$revision = new CredentialRevision();
		$revision->setId($revision_id);
		$revision->setUserId($user_id);
		return $this->delete($revision);
	}
}
