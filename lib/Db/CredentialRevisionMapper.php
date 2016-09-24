<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
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
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function getRevisions($credential_id, $user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_revisions` ' .
			'WHERE `credential_id` = ? and `user_id` = ? ';
		return $this->findEntities($sql, [$credential_id, $user_id]);
	}

	public function create($credential, $userId, $credential_id) {
		$revision = new CredentialRevision();
		$revision->setGuid($this->utils->GUID());
		$revision->setUserId($userId);
		$revision->setCreated($this->utils->getTime());
		$revision->setCredentialId($credential_id);
		$revision->setCredentialData(base64_encode(serialize($credential)));
		return $this->insert($revision);
	}

	public function deleteFile($revision_id, $user_id) {
		$file = new CredentialRevision();
		$file->setId($revision_id);
		$file->setUserId($user_id);
		$this->delete($file);
	}
}