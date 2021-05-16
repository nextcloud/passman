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
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class FileMapper extends QBMapper {
	const TABLE_NAME = 'passman_files';
	private Utils $utils;

	public function __construct(IDBConnection $db, Utils $utils) {
		parent::__construct($db, self::TABLE_NAME);
		$this->utils = $utils;
	}


	/**
	 * @param int $file_id
	 * @param string|null $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getFile(int $file_id, string $user_id = null) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($file_id, IQueryBuilder::PARAM_INT)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
	}

	/**
	 * @param string $file_guid
	 * @param string|null $user_id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getFileByGuid(string $file_guid, string $user_id = null) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('guid', $qb->createNamedParameter($file_guid, IQueryBuilder::PARAM_STR)));

		if ($user_id !== null) {
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntity($qb);
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
	 *
	 * @param int $file_id
	 * @param string $userId
	 * @return File|Entity
	 */
	public function deleteFile(int $file_id, string $userId) {
		$file = new File();
		$file->setId($file_id);
		$file->setUserId($userId);
		return $this->delete($file);
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
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function getFilesFromUser(string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}
}
