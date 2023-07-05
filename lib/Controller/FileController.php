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

use OCA\Passman\Service\FileService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class FileController extends ApiController {
	private $userId;

	public function __construct(
		$AppName,
		IRequest $request,
		$UserId,
		private FileService $fileService,
		private LoggerInterface $logger,
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->userId = $UserId;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function uploadFile($data, $filename, $mimetype, $size) {
		$file = array(
			'filename' => $filename,
			'size' => $size,
			'mimetype' => $mimetype,
			'file_data' => $data,
			'user_id' => $this->userId
		);
		return new JSONResponse($this->fileService->createFile($file, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getFile($file_id) {
		return new JSONResponse($this->fileService->getFile($file_id, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function deleteFile($file_id) {
		return new JSONResponse($this->fileService->deleteFile($file_id, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function deleteFiles($file_ids) {
		$failed_file_ids = [];
		if ($file_ids != null && !empty($file_ids)) {
			$decoded_file_ids = json_decode($file_ids);
			foreach ($decoded_file_ids as $file_id) {
				try {
					$this->fileService->deleteFile($file_id, $this->userId);
				} catch (\Exception $e) {
					$this->logger->error('Error deleting file (' . $file_id . ') in filecontroller:deleteFiles()',
						['exception' => $e->getTrace(), 'message' => $e->getMessage()]);
					$failed_file_ids[] = $file_id;
					continue;
				}
			}
		}
		return new JSONResponse(array('ok' => empty($failed_file_ids), 'failed' => $failed_file_ids));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function updateFile($file_id, $file_data, $filename) {
		try {
			$file = $this->fileService->getFile($file_id, $this->userId);
		} catch (\Exception $doesNotExistException) {

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
