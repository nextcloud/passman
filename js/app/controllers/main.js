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
		.controller('MainCtrl', ['$scope', '$rootScope', '$location', 'SettingsService', '$window', '$interval', '$filter', function ($scope, $rootScope, $location, SettingsService, $window, $interval, $filter) {
			$scope.selectedVault = false;

			$scope.http_warning_hidden = true;
			if ($location.$$protocol === 'http' && $location.$$host !== 'localhost' && $location.$host !== '127.0.0.1') {
				$scope.using_http = true;
				$scope.http_warning_hidden = false;

			}

            $scope.removeHiddenStyles = function(){
				document.getElementById('warning_bar').classList.remove('template-hidden');
			};

			$rootScope.$on('settings_loaded', function(){
				if (SettingsService.isEnabled('disable_contextmenu')) {
					document.addEventListener('contextmenu', function (event) {
						event.preventDefault();
					});
				}
				if (SettingsService.isEnabled('https_check')) {
					$scope.http_warning_hidden = true;
				}

				if(SettingsService.isEnabled('disable_debugger')){
					(function a() {
						try {
							(function b(i) {
								if (('' + (i / i)).length !== 1 || i % 20 === 0) {
									(function() {}).constructor('debugger')();
								} else {
									// This debugger statement is allowed to block javascript console
									/*jshint -W087 */
									debugger;
								}
								b(++i);
							})(0);
						} catch (e) {
							setTimeout(a, 5000);
						}
					})();
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

			var tickSessionTimer = function(){
				if($scope.session_time_left){
					$scope.session_time_left--;
					var session_time_left_formatted = $filter('toHHMMSS')($scope.session_time_left);
					$scope.translationData = {
						session_time: session_time_left_formatted
					};
					$rootScope.$broadcast('logout_timer_tick_tack', $scope.session_time_left);
					if($scope.session_time_left === 0){
						$window.location.reload();
					}
				}
			};

			$scope.session_time_left = false;
			$scope.$on('logout_timer_set', function(evt, timer){
				$scope.session_time_left = timer;
				$scope.translationData = {
					session_time: timer
				};
				$interval(tickSessionTimer, 1000);
			});

		}]);

}());