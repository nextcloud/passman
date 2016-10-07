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

namespace OCA\Passman\Service;

use OCA\Passman\Db\CredentialRevision;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\CredentialRevisionMapper;


class CredentialRevisionService {

	private $credentialRevisionMapper;

	public function __construct(CredentialRevisionMapper $credentialRevisionMapper) {
		$this->credentialRevisionMapper = $credentialRevisionMapper;
	}

	public function createRevision($credential, $userId, $credential_id, $edited_by) {
		return $this->credentialRevisionMapper->create($credential, $userId, $credential_id, $edited_by);
	}

	public function getRevisions($credential_id, $user_id = null) {
		return $this->credentialRevisionMapper->getRevisions($credential_id, $user_id);
	}

	public function getRevision($credential_id, $user_id = null) {
		return $this->credentialRevisionMapper->getRevision($credential_id, $user_id);
	}

	public function deleteRevision($revision_id, $user_id) {
		return $this->credentialRevisionMapper->deleteRevision($revision_id, $user_id);
	}

	public function updateRevision(CredentialRevision $credentialRevision) {
		return $this->credentialRevisionMapper->update($credentialRevision);
	}
}