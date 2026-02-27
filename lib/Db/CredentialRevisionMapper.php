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
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<CredentialRevision>
 */
class CredentialRevisionMapper extends QBMapper {
	const TABLE_NAME = 'passman_revisions';

	public function __construct(IDBConnection $db, private readonly Utils $utils) {
		parent::__construct($db, self::TABLE_NAME);
	}


	/**
	 * Get revisions from a credential
	 *
	 * @param int $credential_id
	 * @param string|null $user_id
	 * @return CredentialRevision[]
	 */
	public function getRevisions(int $credential_id, ?string $user_id = null): array {
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
	 * @return CredentialRevision
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getRevision(int $revision_id, ?string $user_id = null): CredentialRevision {
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
	 * @param mixed $credential
	 * @param string $userId
	 * @param int $credential_id
	 * @param string $edited_by
	 * @return CredentialRevision
	 */
	public function create(mixed $credential, string $userId, int $credential_id, string $edited_by): CredentialRevision {
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
	 * @param int $revision_id
	 * @param string $user_id
	 * @return CredentialRevision
	 */
	public function deleteRevision(int $revision_id, string $user_id): CredentialRevision {
		$revision = new CredentialRevision();
		$revision->setId($revision_id);
		$revision->setUserId($user_id);
		return $this->delete($revision);
	}
}
