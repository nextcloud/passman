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

use OCA\Passman\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {

    public function __construct(IRequest $request) {
        parent::__construct(Application::APP_ID, $request);
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
		return new TemplateResponse($this->appName, 'main');  // templates/main.php
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function bookmarklet($url='', $title='') {
		$params = ['url' => $url, 'title' => $title];
		return new TemplateResponse($this->appName, 'bookmarklet', $params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function publicSharePage() {
		return new TemplateResponse($this->appName, 'public_share', [], TemplateResponse::RENDER_AS_PUBLIC);
	}

}
