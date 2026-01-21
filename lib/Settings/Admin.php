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
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use Psr\Log\LoggerInterface;

class Admin implements ISettings {

	/**
	 * Admin constructor.
	 * @param IConfig $config
	 * @param IL10N $l
	 * @param IAppManager $appManager
	 */
	public function __construct(
		protected IConfig $config,
		private IL10N $l,
		private IAppManager $appManager,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$hasInternetConnection = $this->config->getSystemValue('has_internet_connection', true);
		$checkVersion = $this->config->getAppValue('passman', 'check_version', '1') === '1';
		$localVersion = $this->appManager->getAppInfo('passman')["version"];
		$githubVersion = $this->l->t('Unable to get version info');
		$githubReleaseUrl = null;

		if ($checkVersion && $hasInternetConnection) {
			// get latest GitHub release version

			$url = 'https://api.github.com/repos/nextcloud/passman/releases/latest';
			try {
				$httpClient = new Client();
				$response = $httpClient->request('get', $url);
				$json = $response->getBody()->getContents();

				if ($json) {
					$data = json_decode($json);
					if (isset($data->tag_name) && is_string($data->tag_name)) {
						$githubVersion = $data->tag_name;

						if (isset($data->html_url) && is_string($data->html_url)) {
							$githubReleaseUrl = $data->html_url;
						}
					}
				}
			} catch (\Exception $e) {
				$this->logger->error('Error fetching latest GitHub release version in lib/Admin:getForm()',
					['exception' => $e->getTrace(), 'message' => $e->getMessage()]);
			}
		}

		return new TemplateResponse('passman', 'admin', [
			'localVersion' => $localVersion,
			'githubVersion' => $githubVersion,
			'githubReleaseUrl' => $githubReleaseUrl,
			'checkVersion' => $checkVersion,
			'hasInternetConnection' => $hasInternetConnection,
		], 'blank');
	}

	/**
	 * @return string
	 */
	public function getSection(): string {
		return 'passman';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 100;
	}
}
