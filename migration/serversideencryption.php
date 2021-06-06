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
use OCA\Passman\Db\File;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\FileService;
use OCP\DB\Exception;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use Psr\Log\LoggerInterface;


class ServerSideEncryption implements IRepairStep {

	/** @var EncryptService */
	private $encryptService;

	/** @var IDBConnection */
	private $db;

	/** @var string */
	private $installedVersion;

	/** @var LoggerInterface */
	private $logger;

	/** @var CredentialService */
	private $credentialService;

	/** @var  CredentialRevisionService */
	private $revisionService;

	/** @var FileService */
	private $fileService;

	public function __construct(EncryptService $encryptService, IDBConnection $db, LoggerInterface $logger, CredentialService $credentialService, CredentialRevisionService $revisionService,
	                            FileService $fileService, IConfig $config) {
		$this->encryptService = $encryptService;
		$this->db = $db;
		$this->logger = $logger;
		$this->credentialService = $credentialService;
		$this->revisionService = $revisionService;
		$this->fileService = $fileService;
		$this->installedVersion = $config->getAppValue('passman', 'installed_version');
	}

	public function getName() {
		return 'Enabling server side encryption for passman';
	}

	public function run(IOutput $output) {
		$output->info('Enabling Service Side Encryption for passman');

		if (version_compare($this->installedVersion, '2.0.0RC4', '<')) {
			$this->encryptCredentials();
			$this->encryptRevisions();
			$this->encryptFiles();
		}
	}

	/**
	 * KEEP THIS METHOD PRIVATE!!!
	 *
	 * @param string $table
	 * @return mixed[]
	 * @throws Exception
	 */
	private function fetchAll(string $table) {
		$qb = $this->db->getQueryBuilder();
		$result = $qb->select('*')
			->from($table)
			->execute();
		return $result->fetchAll();
	}

	private function encryptCredentials() {
		$credentials = $this->fetchAll('passman_credentials');
		foreach ($credentials as $credential) {
			$this->credentialService->updateCredential($credential);
		}
	}

	private function encryptRevisions() {
		$revisions = $this->fetchAll('passman_revisions');
		foreach ($revisions as $_revision) {
			$revision = new CredentialRevision();
			$revision->setId($_revision['id']);
			$revision->setGuid($_revision['guid']);
			$revision->setCredentialId($_revision['credential_id']);
			$revision->setUserId($_revision['user_id']);
			$revision->setCreated($_revision['created']);
			$revision->setEditedBy($_revision['edited_by']);
			$revision->setCredentialData($_revision['credential_data']);
			$this->revisionService->updateRevision($revision);
		}
	}

	private function encryptFiles() {
		$files = $this->fetchAll('passman_files');
		foreach ($files as $_file) {
			$file = new File();
			$file->setId($_file['id']);
			$file->setGuid($_file['guid']);
			$file->setUserId($_file['user_id']);
			$file->setMimetype($_file['minetype']);
			$file->setFilename($_file['filename']);
			$file->setSize($_file['size']);
			$file->setCreated($_file['created']);
			$file->setFileData($_file['file_data']);
			$this->fileService->updateFile($file);
		}
	}
}
