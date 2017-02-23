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
		.controller('RequestDeleteCtrl', ['$scope', '$location', '$http', '$routeParams', 'VaultService', 'NotificationService', '$translate',
			function ($scope, $location, $http, $routeParams, VaultService, NotificationService, $translate) {
				$scope.reason = '';
				VaultService.getVault({guid: $routeParams.vault_id}).then(function(vault){
					$scope.pending_deletion = vault.delete_request_pending;
				});

				$scope.requestDeletion = function () {
					var queryUrl = OC.generateUrl('apps/passman/admin/request-deletion/'+ $routeParams.vault_id);
					var params = {
						reason: $scope.reason
					};

					$http.post(queryUrl, params).then(function () {
						NotificationService.showNotification($translate.instant('deletion.requested'), 5000);
						$location.path('#/');
					});
				};

				$scope.removeRequestDeletion = function () {
					var queryUrl = OC.generateUrl('apps/passman/admin/request-deletion/' + $routeParams.vault_id);
					$http.delete(queryUrl).then(function () {
						NotificationService.showNotification($translate.instant('deletion.removed'), 5000);
						$location.path('#/');
					});
				};
			}]);
}());