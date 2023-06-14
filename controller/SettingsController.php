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

use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCP\IRequest;
use OCA\Passman\Service\SettingsService;

class SettingsController extends ApiController {
	private $userId;
	private $settings;

	public function __construct(
		$AppName,
		IRequest $request,
		$userId,
		SettingsService $settings,
		IL10N $l) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->settings = $settings;
		$this->l = $l;
		$this->userId = $userId;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		return new TemplateResponse('passman', 'part.admin');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'additional';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority() {
		return 0;
	}

	/**
	 * Get all settings
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getSettings() {
		$settings = $this->settings->getAppSettings();
		return new JSONResponse($settings);
	}

	/**
	 * Save a user setting
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function saveUserSetting($key, $value) {
		$this->settings->setUserSetting($key, $value);
		return new JSONResponse('OK');
	}


	/**
	 * Save a app setting
	 *
	 * @NoCSRFRequired
	 */
	public function saveAdminSetting($key, $value) {
		$this->settings->setAppSetting($key, $value);
		return new JSONResponse('OK');
	}

}