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

namespace OCA\Passman\Controller;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\FileService;

class FileController extends ApiController {
	private $userId;
	private $fileService;
	public function __construct($AppName,
								IRequest $request,
								$UserId,
								FileService $fileService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->fileService = $fileService;
	}


	/**
	 * @NoAdminRequired
	 */
	public function uploadFile($data, $filename, $mimetype, $size) {
		$file = array(
			'filename' => $filename,
			'size' => $size,
			'mimetype' => $mimetype,
			'file_data' => $data
		);
		return new JSONResponse($this->fileService->createFile($file, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function getFile($file_id) {
		return new JSONResponse($this->fileService->getFile($file_id, $this->userId));
	}
	/**
	 * @NoAdminRequired
	 */
	public function deleteFile($file_id) {
		return new JSONResponse($this->fileService->deleteFile($file_id, $this->userId));
	}

	public function updateFile($file_id, $file_data, $filename, $mimetype, $size) {
		try {
			$file = $this->fileService->getFile($file_id, $this->userId);
		} catch (DoesNotExistException $doesNotExistException) {

		}
		if ($file) {
			if ($file_data) {
				$file->setFileData($file_data);
			}
			if ($filename) {
				$file->setFilename($filename);
			}
			if ($filename || $file_data) {
				new JSONResponse($this->fileService->updateFile($file));
			}
		}
	}
}