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
	 * @ngdoc directive
	 * @name passmanApp.directive:passwordGen
	 * @description
	 * # passwordGen
	 */
	angular.module('passmanApp')
		.directive('credentialCounter', [function () {
			return {
				template: '<div ng-show="counter" translate="number.filtered" translate-values="{number_filtered: counter, credential_number: total}"></div>',
				replace: false,
				restrict: 'A',
				scope: {
					credentials: '=credentialCounter',
					deleteTime: '=',
					vault: '=',
					filters: '='
				},

				link: function (scope) {
					function countCredentials() {
						var countedCredentials = 0;
						var total = 0;
						angular.forEach(scope.credentials, function (credential) {
							total = (credential.hidden !== 1) ? total + 1 : total;
							if(credential.delete_time >= scope.deleteTime && credential.hidden === 0){
								countedCredentials = countedCredentials+1;
							}
						});
						scope.counter = countedCredentials;
						scope.total = total;
					}
					scope.$watch('[credentials, deleteTime, filters]', function () {
						countCredentials();
					}, true);
				}
			};
		}]);
}());