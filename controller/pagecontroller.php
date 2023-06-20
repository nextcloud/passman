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

use OCP\AppFramework\Http\StrictContentSecurityPolicy;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = ['user' => $this->userId];
		$response = new TemplateResponse('passman', 'main', $params);  // templates/main.php

		$csp = new StrictContentSecurityPolicy();
		$csp->allowEvalScript();
		$csp->allowInlineStyle();

		$response->setContentSecurityPolicy($csp);

		return $response;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function bookmarklet($url='',$title='') {
		$params = array('url' => $url, 'title' => $title);
		return new TemplateResponse('passman', 'bookmarklet', $params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function publicSharePage() {
		return new TemplateResponse('passman', 'public_share');
	}

}