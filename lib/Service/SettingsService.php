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

namespace OCA\Passman\Service;

use OCP\IConfig;


class SettingsService {

	private $userId;
	private $config;
	private $appName;
	public $settings;

	private $numeric_settings = array(
		'link_sharing_enabled',
		'user_sharing_enabled',
		'vault_key_strength',
		'check_version',
		'https_check',
		'disable_contextmenu',
		'enable_global_search',
		'settings_loaded'
	);

	public function __construct($UserId, IConfig $config, $AppName) {
		$this->userId = $UserId;
		$this->config = $config;
		$this->appName = $AppName;
		$this->settings = array(
			'link_sharing_enabled' => intval($this->config->getAppValue('passman', 'link_sharing_enabled', 1)),
			'user_sharing_enabled' => intval($this->config->getAppValue('passman', 'user_sharing_enabled', 1)),
			'vault_key_strength' => intval($this->config->getAppValue('passman', 'vault_key_strength', 3)),
			'check_version' => intval($this->config->getAppValue('passman', 'check_version', 1)),
			'https_check' => intval($this->config->getAppValue('passman', 'https_check', 1)),
			'disable_contextmenu' => intval($this->config->getAppValue('passman', 'disable_contextmenu', 1)),
			'server_side_encryption' => $this->config->getAppValue('passman', 'server_side_encryption', 'aes-256-cbc'),
			'rounds_pbkdf2_stretching' => $this->config->getAppValue('passman', 'rounds_pbkdf2_stretching', 100),
			'disable_debugger' => $this->config->getAppValue('passman', 'disable_debugger', 1),
			'enable_global_search' => $this->config->getAppValue('passman', 'enable_global_search', 0),
			'settings_loaded' => 1
		);
	}

	/**
	 * Get all app settings
	 *
	 * @return array
	 */
	public function getAppSettings() {
		return $this->settings;
	}

	/**
	 * Get a app setting
	 *
	 * @param $key string
	 * @param null $default_value The default value if key does not exist
	 * @return mixed
	 */
	public function getAppSetting($key, $default_value = null) {
		$value = ($this->settings[$key]) ? $this->settings[$key] : $this->config->getAppValue('passman', $key, $default_value);
		if (in_array($key, $this->numeric_settings)) {
			$value = intval($value);
		}

		return $value;
	}

	/**
	 * Set a app setting
	 *
	 * @param $key string Setting name
	 * @param $value mixed Value of the setting
	 */
	public function setAppSetting($key, $value) {
		$this->settings[$key] = $value;
		$this->config->setAppValue('passman', $key, $value);
	}

	/**
	 * Set a user setting
	 *
	 * @param $key string Setting name
	 * @param $value mixed Value of the setting
	 */

	public function setUserSetting($key, $value) {
		return $this->config->setUserValue($this->userId, $this->appName, $key, $value);
	}

	/**
	 * Check if an setting is enabled (value of 1)
	 *
	 * @param $setting
	 * @return bool
	 */
	public function isEnabled($setting) {
		$value = intval($this->getAppSetting($setting, false));
		return ($value === 1);
	}
}
