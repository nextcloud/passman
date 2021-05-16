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
		.controller('PublicSharedCredential', ['$scope', 'ShareService', '$window', 'EncryptService', 'NotificationService', '$translate', 'escapeHTMLFilter', function ($scope, ShareService, $window, EncryptService, NotificationService, $translate, escapeHTMLFilter) {
			var _key;
			$scope.loading = false;
			$scope.loadSharedCredential = function () {
				$scope.loading = true;
				var data = window.atob($window.location.hash.replace('#', '')).split('<::>');
				var guid = data[0];
				_key = data[1];
				ShareService.getPublicSharedCredential(guid).then(function (sharedCredential) {
					$scope.loading = false;
					if (sharedCredential.status === 200) {
						$scope.shared_credential = ShareService.decryptSharedCredential(sharedCredential.data.credential_data, _key);
					} else {
						$scope.expired = true;
					}

				});

			};

			$scope.downloadFile = function (credential, file) {
				ShareService.downloadSharedFile(credential, file).then(function (result) {
					if (!result.hasOwnProperty('file_data')) {
						NotificationService.showNotification($translate.instant('error.loading.file.perm'), 5000);
						return;
					}
					var file_data = EncryptService.decryptString(result.file_data, _key);
					download(file_data, escapeHTMLFilter(file.filename), file.mimetype);
				});
			};
		}]);
}());
