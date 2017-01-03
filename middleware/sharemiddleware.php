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
			$http_response_code = Http::STATUS_OK;
			$result = array();
			$publicMethods = array('createPublicShare', 'getPublicCredentialData');
			$user_pub_methods = array('updateSharedCredentialACL', 'getFile', 'getItemAcl');
			$setting = (in_array($methodName, $publicMethods)) ? 'link_sharing_enabled' : 'user_sharing_enabled';
			$sharing_enabled = ($this->settings->isEnabled($setting));

			if(in_array($methodName, $user_pub_methods)){
				$sharing_enabled = ($this->settings->isEnabled('link_sharing_enabled') || $this->settings->isEnabled('user_sharing_enabled'));
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


