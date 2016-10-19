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