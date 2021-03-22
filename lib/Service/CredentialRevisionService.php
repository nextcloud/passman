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

namespace OCA\Passman\Service;

use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;


class CredentialRevisionService {

	private CredentialRevisionMapper $credentialRevisionMapper;
	private EncryptService $encryptService;
	private $server_key;

	public function __construct(CredentialRevisionMapper $credentialRevisionMapper, EncryptService $encryptService, IConfig $config) {
		$this->credentialRevisionMapper = $credentialRevisionMapper;
		$this->encryptService = $encryptService;
		$this->server_key = $config->getSystemValue('passwordsalt', '');
	}

	/**
	 * Create a new revision for a credential
	 *
	 * @param $credential
	 * @param $userId
	 * @param $credential_id
	 * @param $edited_by
	 * @return CredentialRevision
	 * @throws \Exception
	 */
	public function createRevision($credential, $userId, $credential_id, $edited_by) {
		$credential = $this->encryptService->encryptCredential($credential);
		return $this->credentialRevisionMapper->create($credential, $userId, $credential_id, $edited_by);
	}

	/**
	 * Get revisions of a credential
	 *
	 * @param int $credential_id
	 * @param string|null $user_id
	 * @return Entity[]
	 * @throws \Exception
	 */
	public function getRevisions(int $credential_id, string $user_id = null) {
		$result = $this->credentialRevisionMapper->getRevisions($credential_id, $user_id);
		foreach ($result as $index => $revision) {
			$c = json_decode(base64_decode($revision->getCredentialData()), true);
			$result[$index] = $revision->jsonSerialize();
			$result[$index]['credential_data'] = $this->encryptService->decryptCredential($c);
		}
		return $result;
	}

	/**
	 * @param int $credential_id
	 * @param string|null $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws \Exception
	 */
	public function getRevision(int $credential_id, string $user_id = null) {
		$revision = $this->credentialRevisionMapper->getRevision($credential_id, $user_id);
		$c = json_decode(base64_decode($revision->getCredentialData()), true);
		$revision->setCredentialData($this->encryptService->decryptCredential($c));
		return $revision;
	}

	/**
	 * Delete a revision
	 *
	 * @param int $revision_id
	 * @param string $user_id
	 * @return CredentialRevision
	 */
	public function deleteRevision(int $revision_id, string $user_id) {
		return $this->credentialRevisionMapper->deleteRevision($revision_id, $user_id);
	}

	/**
	 * Update revision
	 *
	 * @param CredentialRevision $credentialRevision
	 * @return CredentialRevision|Entity
	 * @throws \Exception
	 */
	public function updateRevision(CredentialRevision $credentialRevision) {
		$credential_data = $credentialRevision->getCredentialData();
		$credential_data = json_decode(base64_decode($credential_data), true);
		$credential_data = base64_encode(json_encode($this->encryptService->encryptCredential($credential_data)));
		$credentialRevision->setCredentialData($credential_data);
		return $this->credentialRevisionMapper->update($credentialRevision);
	}
}
