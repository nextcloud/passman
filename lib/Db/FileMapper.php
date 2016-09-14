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
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 */
	public function getFile($itemId, $fileId) {
		$sql = 'SELECT * FROM `*PREFIX*passman_files` ' .
			'WHERE `id` = ? and `item_id` = ? ';
		return $this->findEntities($sql, [$fileId, $itemId]);
	}

	public function create($file_raw, $userId){
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

	public function deleteFile($file_id, $userId) {
		$file = new File();
		$file->setId($file_id);
		$file->setUserId($userId);
		$this->delete($file);
	}
}