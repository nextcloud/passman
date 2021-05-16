<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Passman\Settings;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	protected IConfig $config;
	private IL10N $l;
	private IAppManager $appManager;

	/**
	 * Admin constructor.
	 * @param IConfig $config
	 * @param IL10N $l
	 * @param IAppManager $appManager
	 */
	public function __construct(IConfig $config, IL10N $l, IAppManager $appManager) {
		$this->config = $config;
		$this->l = $l;
		$this->appManager = $appManager;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$checkVersion = $this->config->getAppValue('passman', 'check_version', '1') === '1';
		$localVersion = $this->appManager->getAppInfo('passman')["version"];
		$githubVersion = $this->l->t('Unable to get version info');
		if ($checkVersion) {
			// get latest master version
			$version = false;

			$url = 'https://raw.githubusercontent.com/nextcloud/passman/dist/appinfo/info.xml';
			try {
				$httpClient = new Client();
				$response = $httpClient->request('get', $url);
				$xml = $response->getBody()->getContents();
			} catch (GuzzleException $e) {
				$xml = false;
			}

			if ($xml) {
				$data = simplexml_load_string($xml);

				// libxml_disable_entity_loader is deprecated with php8, the vulnerability is disabled by default by libxml with php8
				if (\PHP_VERSION_ID < 80000) {
					$loadEntities = libxml_disable_entity_loader(true);
					$data = simplexml_load_string($xml);
					libxml_disable_entity_loader($loadEntities);
				}

				if ($data !== false) {
					$version = (string)$data->version;
				} else {
					libxml_clear_errors();
				}
			}

			if ($version !== false) {
				$githubVersion = $version;
			}
		}
		// $ciphers = openssl_get_cipher_methods();

		return new TemplateResponse('passman', 'admin', [
			'localVersion' => $localVersion,
			'githubVersion' => $githubVersion,
			'checkVersion' => $checkVersion,
		], 'blank');
	}

	/**
	 * @return string
	 */
	public function getSection(): string {
		return 'additional';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 100;
	}
}
