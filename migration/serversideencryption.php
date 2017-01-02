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

namespace OCA\Passman\Migration;

use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\FileService;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;


class ServerSideEncryption implements IRepairStep {

	/** @var encryptService */
	private $encryptService;

	/** @var IDBConnection */
	private $db;

	/** @var string */
	private $installedVersion;

	/** @var ILogger */
	private $logger;

	/** @var CredentialService */
	private $credentialService;

	/** @var  CredentialRevisionService */
	private $revisionService;

	/** @var FileService */
	private $fileService;

	public function __construct(EncryptService $encryptService, IDBConnection $db, ILogger $logger, CredentialService $credentialService, CredentialRevisionService $revisionService,
								FileService $fileService) {
		$this->encryptService = $encryptService;
		$this->db = $db;
		$this->logger = $logger;
		$this->credentialService = $credentialService;
		$this->revisionService = $revisionService;
		$this->fileService = $fileService;
		$this->installedVersion = \OC::$server->getConfig()->getAppValue('passman', 'installed_version');
	}

	public function getName() {
		return 'Enabling server side encryption for passman';
	}

	public function run(IOutput $output) {
		if (version_compare($this->installedVersion, '2.0.0RC4', '<')) {
			$this->encryptCredentials();
			$this->encryptRevisions();
			$this->encryptFiles();
		}
	}

	private function encryptCredentials() {
		$credentials = $this->db->executeQuery('SELECT * FROM `*PREFIX*passman_credentials`')->fetchAll();
		foreach ($credentials as $credential) {
			$this->credentialService->updateCredential($credential);
		}
	}

	private function encryptRevisions() {
		$revisions = $this->db->executeQuery('SELECT * FROM `*PREFIX*passman_revisions`')->fetchAll();
		foreach ($revisions as $_revision) {
			$revision = new CredentialRevision();
			$revision->setId($_revision['id']);
			$revision->setGuid($_revision['guid']);
			$revision->setCredentialId($_revision['credential_id']);
			$revision->setUserId($_revision['user_id']);
			$revision->setCreated($_revision['created']);
			$revision->setEditedBy($_revision['edited_by']);
			$revision->setCredentialData( $_revision['credential_data']);
			$this->revisionService->updateRevision($revision);
		}
	}

	private function encryptFiles() {
		$files = $this->db->executeQuery('SELECT * FROM `*PREFIX*passman_files`')->fetchAll();
		foreach ($files as $file) {
			$this->fileService->updateFile($file);
		}
	}
}
