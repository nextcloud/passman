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

use Exception;
use OCA\Passman\Db\File;
use OCA\Passman\Db\FileMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;


class FileService {

	private FileMapper $fileMapper;
	private EncryptService $encryptService;
	private $server_key;

	public function __construct(FileMapper $fileMapper, EncryptService $encryptService, IConfig $config) {
		$this->fileMapper = $fileMapper;
		$this->encryptService = $encryptService;
		$this->server_key = $config->getSystemValue('passwordsalt', '');
	}

	/**
	 * Get a single file. This function also returns the file content.
	 *
	 * @param int $fileId
	 * @param string|null $userId
	 * @return array|File
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function getFile(int $fileId, string $userId = null) {
		$file = $this->fileMapper->getFile($fileId, $userId);
		return $this->encryptService->decryptFile($file);
	}

	/**
	 * Get a single file. This function also returns the file content.
	 *
	 * @param string $file_guid
	 * @param string|null $userId
	 * @return array|File
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function getFileByGuid(string $file_guid, string $userId = null) {
		$file = $this->fileMapper->getFileByGuid($file_guid, $userId);
		return $this->encryptService->decryptFile($file);
	}

	/**
	 * Upload a new file,
	 *
	 * @param array $file
	 * @param string $userId
	 * @return array|File
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function createFile(array $file, string $userId) {
		$file = $this->encryptService->encryptFile($file);
		$file = $this->fileMapper->create($file, $userId);
		return $this->getFile($file->getId());
	}

	/**
	 * Delete file
	 *
	 * @param int $file_id
	 * @param string $userId
	 * @return File|Entity
	 */
	public function deleteFile(int $file_id, string $userId) {
		return $this->fileMapper->deleteFile($file_id, $userId);
	}

	/**
	 * Update file
	 *
	 * @param File $file
	 * @return File
	 * @throws Exception
	 */
	public function updateFile(File $file) {
		$file = $this->encryptService->encryptFile($file);
		return $this->fileMapper->updateFile($file);
	}

	/**
	 * Update file
	 *
	 * @param string $userId
	 * @return File[]
	 * @throws Exception
	 */
	public function getFilesFromUser(string $userId) {
		$files = $this->fileMapper->getFilesFromUser($userId);
		$results = array();
		foreach ($files as $file) {
			array_push($results, $this->encryptService->decryptFile($file));
		}
		return $results;
	}
}
