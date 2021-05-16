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

use Doctrine\DBAL\Exception\DriverException;
use OC\App\AppManager;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\IconService;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

class IconController extends ApiController {
	private $userId;
	private $credentialService;
	private $am;
	private $urlGenerator;

	public function __construct($AppName,
	                            IRequest $request,
	                            $UserId,
	                            CredentialService $credentialService,
	                            AppManager $am,
	                            IURLGenerator $urlGenerator
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
		$this->am = $am;
		$this->urlGenerator = $urlGenerator;

	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getSingleIcon($base64Url) {
		$url = base64_decode(str_replace('_', '/', $base64Url));
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}


		$icon = new IconService($url);

		if ($icon->icoExists) {
			$icon_json['type'] = $icon->icoType;
			$icon_json['content'] = base64_encode($icon->icoData);
			return new JSONResponse($icon_json);
		}

		return new JSONResponse();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getIcon($base64Url, $credentialId) {
		$url = base64_decode(str_replace('_', '/', $base64Url));

		if ($credentialId && $credentialId != "null") {
			try {
				$credential = $this->credentialService->getCredentialById($credentialId, $this->userId);
				$credential = $credential->jsonSerialize();
			} catch (DoesNotExistException $e) {
				// Credential is not found, continue
				$credential = false;
			}
		}

		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}

		$data = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAMAAACdt4HsAAABHVBMVEUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADF3oJhAAAAXnRSTlMAAQIDBAUGBwgJCwwOEBITFBUWFxgaHB4hJCUnKissMDI0ODs9PkFCQ0RNUVJWV1lbXF1hY2Zna2xtcXh7f4KDhYmUm52lq62vsLW3ucHFyszO0dPV197i7/H3+fv9358zuQAAAWdJREFUWMPtldlWwjAURdPWogyKOKM4z0NRQRRHnAdE0QoI1eb/P8OnmzYlSZs+unIes+/ZbdOuFCFuBmc2Dk+qpe18EsVIptTGJJ3jrGR99B4H8jQlUTfOMSM3ZtT+SAsz8z0ZrZ//wZy4S1H6C1iQtfD+tCsS4EJYP9kV9rGTCRE0fMOfxZypITO7++5b/NCE/S3fx7PsLc9/eeuWqK/3vA9ngAJ3BPwmBIIdMnYbvNNLgo4Egg4MvelBpD0D6/F3YYJcJd0PEw7AWa6gCCNnLLoPtMoVPMJIikVNoE2uAN6BzcZ1MPA2wRA+AUIHwHkn1BAM7LH5OvBhjiAFA6tsXgCe4wjSMLDC5nPAx5Xg3wrGylfk1GlcM/MC/KFW6fvRVbBkLuj+omwf401KUJcXtCiBIy+gT4UYfawrgRIogRIogRLwBG4MAfVnsuX7XX8fWfKCU0qgvcr2mwaiDZYtsw/tMtnCP4F4Y01BhTeiAAAAAElFTkSuQmCC");
		$type = 'png';

		try {
			$icon = new IconService($url);
			if ($icon->icoExists) {
				$data = $icon->icoData;
				$type = $icon->icoType;
			}
		} catch (\InvalidArgumentException $e) {
			//no need to do stuff in catch
			//if IconService fails the predefined $data and $type are used
		}

		if (isset($credential) && $credential['user_id'] == $this->userId) {
			$iconData = [
				'type' => ($type) ? $type : 'x-icon',
				'content' => base64_encode($data)
			];
			$credential['icon'] = json_encode($iconData);
			try {
				if ($credential) {
					$this->credentialService->updateCredential($credential);
				}
			} catch (DriverException $exception) {
				/**
				 * @FIXME Syntax error or access violation: 1118 Row size too large
				 * This happens when favicons are quite big.
				 * Githubs one is 33kb and triggers the try catch
				 */
			}
		}


		$offset = 3600 * 24 * 30;
		$contentType = 'image/png';
		$response = new DataDownloadResponse($data, 'icon', $contentType);

		$response->addHeader('Content-Type', $contentType);
		$response->addHeader('Content-Length:', mb_strlen($data));
		$response->addHeader('Expires: ', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
		$response->setETag($base64Url);
		$response->cacheFor($offset);

		return $response;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getLocalIconList() {
		$dir = $this->am->getAppPath('passman');
		$result = Utils::getDirContents($dir . '/img/icons');

		$icons = [];
		foreach ($result as $icon) {
			$iconPath = $icon;
			$path = explode('passman/', $iconPath);
			$pack = explode('/', $path[1])[2];
			$mime = mime_content_type($iconPath);
			//print_r($path);
			if ($mime !== 'directory') {
				$icon = [];
				$icon['mimetype'] = mime_content_type($iconPath);
				$icon['url'] = $this->urlGenerator->linkTo('passman', $path[1]);
				$icon['pack'] = $pack;
				if (!isset($icons[$pack])) {
					$icons[$pack] = [];
				}
				$icons[$pack][] = $icon;
			}
		}
		return new JSONResponse($icons);
	}
}
