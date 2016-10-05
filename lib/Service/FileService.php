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

use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\FileMapper;


class FileService {

	private $fileMapper;

	public function __construct(FileMapper $fileMapper) {
		$this->fileMapper = $fileMapper;
	}

	public function getFile($fileId, $userId = null) {
		return $this->fileMapper->getFile($fileId, $userId);
	}

	public function getFileByGuid($file_guid, $userId = null) {
		return $this->fileMapper->getFileByGuid($file_guid, $userId);
	}

	public function createFile($file, $userId) {
		return $this->fileMapper->create($file, $userId);
	}

	public function deleteFile($file_id, $userId) {
		return $this->fileMapper->deleteFile($file_id, $userId);
	}

	public function updateFile($file_id) {
		return $this->fileMapper->updateFile($file_id);
	}

}