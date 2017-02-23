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

class FileMapper extends Mapper {
	private $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, 'passman_files');
		$this->utils = $utils;
	}


	/**
	 * @param $file_id
	 * @param null $user_id
	 * @return File
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function getFile($file_id, $user_id = null) {
		$sql = 'SELECT * FROM `*PREFIX*passman_files` ' .
			'WHERE `id` = ?';
		$params = [$file_id];
		if ($user_id !== null) {
			$sql .= ' and `user_id` = ? ';
			array_push($params, $user_id);
		}
		return $this->findEntity($sql, $params);
	}
	/**
	 * @param $file_id
	 * @param null $user_id
	 * @return File
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function getFileByGuid($file_guid, $user_id = null) {
		$sql = 'SELECT * FROM `*PREFIX*passman_files` ' .
			'WHERE `guid` = ?';
		$params = [$file_guid];
		if ($user_id !== null) {
			$sql .= ' and `user_id` = ? ';
			array_push($params, $user_id);
		}
		return $this->findEntity($sql, $params);
	}

	/**
	 * @param $file_raw
	 * @param $userId
	 * @return File
	 */
	public function create($file_raw, $userId) {
		$file = new File();
		$file->setGuid($this->utils->GUID());
		$file->setUserId($userId);
		$file->setFilename($file_raw['filename']);
		$file->setSize($file_raw['size']);
		$file->setCreated($this->utils->getTime());
		$file->setFileData($file_raw['file_data']);
		$file->setMimetype($file_raw['mimetype']);


		return $this->insert($file);
	}

	/**
	 * Delete a file by file_id and user id
	 * @param $file_id
	 * @param $userId
	 * @return File
	 */
	public function deleteFile($file_id, $userId) {
		$file = new File();
		$file->setId($file_id);
		$file->setUserId($userId);
		$this->delete($file);
	}

	/**
	 * Uodate file
	 * @param File $file
	 * @return File
	 */
	public function updateFile(File $file) {
		return $this->update($file);
	}


	/**
	 * @param $user_id
	 * @return File[]
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function getFilesFromUser($user_id) {
		$sql = 'SELECT * FROM `*PREFIX*passman_files` ' .
			'WHERE `user_id` = ?';
		$params = [$user_id];

		return $this->findEntities($sql, $params);
	}
}