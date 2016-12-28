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
	 * @ngdoc function
	 * @name passmanApp.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('MainCtrl', ['$scope', '$rootScope', '$location', 'SettingsService', function ($scope, $rootScope, $location, SettingsService) {
			$scope.selectedVault = false;

			$scope.http_warning_hidden = true;
			if ($location.$$protocol === 'http' && $location.$$host !== 'localhost' && $location.$host !== '127.0.0.1') {
				$scope.using_http = true;
				$scope.http_warning_hidden = false;

			}

			$rootScope.$on('settings_loaded', function(){
				if (SettingsService.getSetting('disable_contextmenu') === '1' || SettingsService.getSetting('disable_contextmenu') === 1) {
					document.addEventListener('contextmenu', function (event) {
						event.preventDefault();
					});
				}
				if (SettingsService.getSetting('https_check') === '0' || SettingsService.getSetting('https_check') === 0) {
					$scope.http_warning_hidden = true;
				}
			});

			$rootScope.setHttpWarning = function (state) {
				$scope.http_warning_hidden = state;
			};

			$rootScope.$on('app_menu', function (evt, shown) {
				$scope.app_sidebar = shown;
			});

			$rootScope.$on('logout', function () {
				$scope.selectedVault = false;
			});
		}]);

}());