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
		.directive('credentialTemplate', ['EncryptService', '$translate', 'FileService', 'ShareService', 'NotificationService', 'CredentialService', 'escapeHTMLFilter',
			function (EncryptService, $translate, FileService, ShareService, NotificationService, CredentialService, escapeHTMLFilter) {
			return {
				templateUrl: 'views/partials/credential_template.html',
				replace: true,
				restrict: 'A',
				scope: {
					credential: '=credentialTemplate'
				},

				link: function (scope, element, attrs) {
					scope.downloadFile = function (credential, file) {
						var callback = function (result) {
							var key = CredentialService.getSharedKeyFromCredential(credential);
							if (!result.hasOwnProperty('file_data')) {
								NotificationService.showNotification($translate.instant('error.loading.file.perm'), 5000);
								return;

							}
							var file_data = EncryptService.decryptString(result.file_data, key);
							download(file_data, escapeHTMLFilter(file.filename), file.mimetype);

						};

						if (!credential.hasOwnProperty('acl')) {
							FileService.getFile(file).then(callback);
						} else {
							ShareService.downloadSharedFile(credential, file).then(callback);
						}

					};

					scope.showLabel = (attrs.hasOwnProperty('showLabel'));
				}
			};
		}]);
}());
