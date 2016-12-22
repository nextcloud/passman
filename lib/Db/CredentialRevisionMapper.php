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
use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class CredentialRevisionMapper extends Mapper {
	private $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, 'passman_revisions');
		$this->utils = $utils;
	}


	/**
	 * Get revisions from a credential
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return CredentialRevision[]
	 */
	public function getRevisions($credential_id, $user_id = null) {
		$sql = 'SELECT * FROM `*PREFIX*passman_revisions` ' .
			'WHERE `credential_id` = ?';
        $params = [$credential_id];
        if ($user_id !== null) {
            $sql.= ' and `user_id` = ? ';
            $params[] = $user_id;
        }
		return $this->findEntities($sql, $params);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @return CredentialRevision
	 */
	public function getRevision($revision_id, $user_id = null) {
		$sql = 'SELECT * FROM `*PREFIX*passman_revisions` ' .
			'WHERE `id` = ?';
        $params = [$revision_id];
        if ($user_id !== null) {
            $sql.= ' and `user_id` = ? ';
            $params[] = $user_id;
        }
		return $this->findEntity($sql, $params);
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