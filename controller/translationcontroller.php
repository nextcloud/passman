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

class TranslationController extends ApiController {
	private $userId;
	private $fileService;
	public function __construct($AppName,
								IRequest $request
								){
		parent::__construct($AppName, $request);
	}


	/**
	 * @NoAdminRequired
	 */
	public function getLanguageStrings(){

	}
}