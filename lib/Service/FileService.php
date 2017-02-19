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

use OCA\Passman\Db\File;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\FileMapper;


class FileService {

	private $fileMapper;
	private $encryptService;
	private $server_key;

	public function __construct(FileMapper $fileMapper, EncryptService $encryptService) {
		$this->fileMapper = $fileMapper;
		$this->encryptService = $encryptService;
		$this->server_key = \OC::$server->getConfig()->getSystemValue('passwordsalt', '');
	}

	/**
	 * Get a single file. This function also returns the file content.
	 *
	 * @param $fileId
	 * @param null $userId
	 * @return \OCA\Passman\Db\File
	 */
	public function getFile($fileId, $userId = null) {
		$file = $this->fileMapper->getFile($fileId, $userId);
		return $this->encryptService->decryptFile($file);
	}

	/**
	 * Get a single file. This function also returns the file content.
	 *
	 * @param $file_guid
	 * @param null $userId
	 * @return \OCA\Passman\Db\File
	 */
	public function getFileByGuid($file_guid, $userId = null) {
		$file = $this->fileMapper->getFileByGuid($file_guid, $userId);
		return $this->encryptService->decryptFile($file);
	}

	/**
	 * Upload a new file,
	 *
	 * @param $file array
	 * @param $userId
	 * @return \OCA\Passman\Db\File
	 */
	public function createFile($file, $userId) {
		$file = $this->encryptService->encryptFile($file);
		$file = $this->fileMapper->create($file, $userId);
		return $this->getFile($file->getId());
	}

	/**
	 * Delete file
	 *
	 * @param $file_id
	 * @param $userId
	 * @return \OCA\Passman\Db\File
	 */
	public function deleteFile($file_id, $userId) {
		return $this->fileMapper->deleteFile($file_id, $userId);
	}

	/**
	 * Update file
	 *
	 * @param File $file
	 * @return \OCA\Passman\Db\File
	 */
	public function updateFile($file) {
		$file = $this->encryptService->encryptFile($file);
		return $this->fileMapper->updateFile($file);
	}

	/**
	 * Update file
	 *
	 * @param string $userId
	 * @return File[]
	 */
	public function getFilesFromUser($userId){
		$files = $this->fileMapper->getFilesFromUser($userId);
		$results = array();
		foreach ($files as $file){
			array_push($results, $this->encryptService->decryptFile($file));
		}
		return $results;
	}
}