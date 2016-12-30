<?php

namespace OCA\Passman\Middleware;

use OCA\Passman\Controller\ShareController;
use OCA\Passman\Service\SettingsService;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Middleware;
use OCP\AppFramework\Http;

class ShareMiddleware extends Middleware {

	private $settings;

	public function __construct(SettingsService $config) {
		$this->settings = $config;
	}


	public function beforeController($controller, $methodName) {
		if ($controller instanceof ShareController) {
			$http_response_code = \OCP\AppFramework\Http::STATUS_FORBIDDEN;
			$result = 'FORBIDDEN';

			if (in_array($methodName, array('updateSharedCredentialACL', 'getFile', 'getItemAcl'))) {
				$sharing_enabled = ($this->settings->isEnabled('link_sharing_enabled') || $this->settings->isEnabled('user_sharing_enabled'));
			} else {
				$publicMethods = array('createPublicShare', 'getPublicCredentialData');
				$setting = (in_array($methodName, $publicMethods)) ? 'link_sharing_enabled' : 'user_sharing_enabled';
				$sharing_enabled = ($this->settings->isEnabled($setting));
				if ($methodName === 'getVaultItems' || $methodName === 'getPendingRequests') {
					$http_response_code = Http::STATUS_OK;
					$result = array();
				}
			}


			if (!$sharing_enabled) {
				$response = new JSONResponse($result);
				http_response_code($http_response_code);
				header('Passman-sharing: disabled');
				header('Passman-method: ShareController.' . $methodName);
				die($response->render());
			}
		}
	}
}


