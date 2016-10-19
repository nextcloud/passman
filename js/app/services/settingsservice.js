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

(function () {
	'use strict';


	/**
	 * @ngdoc service
	 * @name passmanApp.SettingsService
	 * @description
	 * # SettingsService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('SettingsService', ['localStorageService', function (localStorageService) {
			var settings = {
				defaultVault: null,
				defaultVaultPass: null
			};


			var cookie = localStorageService.get('settings');
			settings = angular.merge(settings, cookie);
			return {
				getSettings: function () {
					return settings;
				},
				getSetting: function (name) {
					return settings[name];
				},
				setSetting: function (name, value) {
					settings[name] = value;
					localStorageService.set('settings', settings);
				}
			};
		}]);
}());