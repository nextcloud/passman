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
	 * Created by wolfi on 25/09/16.
	 */
	angular.module('passmanApp')
		.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'EncryptService',
			function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, EncryptService) {
				$scope.active_vault = VaultService.getActiveVault();
				$scope.sharing_keys = angular.copy(ShareService.getSharingKeys());

				$scope.progress = 1;
				$scope.generating = false;


				$scope.available_sizes = [
					{
						size: 1024,
						name: 1024
					},
					{
						size: 2048,
						name: 2048
					},
					{
						size: 4096,
						name: 4096
					}
				];

				$scope.setKeySize = function (size) {
					for (var i = 0; i < $scope.available_sizes.length; i++) {
						if ($scope.available_sizes[i].size === size) {
							$scope.key_size = $scope.available_sizes[i];
							return;
						}
					}
				};

				$scope.setKeySize(2048);

				$scope.generateKeys = function (length) {
					$scope.progress = 1;
					$scope.generating = true;

					ShareService.generateRSAKeys(length).progress(function (progress) {
						$scope.progress = progress > 0 ? 2 : 1;
						$scope.$digest();
					}).then(function (kp) {
						$scope.generating = false;

						var pem = ShareService.rsaKeyPairToPEM(kp);

						$scope.active_vault.private_sharing_key = EncryptService.encryptString(pem.privateKey);
						$scope.active_vault.public_sharing_key = pem.publicKey;

						VaultService.updateSharingKeys($scope.active_vault).then(function () {
							$scope.sharing_keys = ShareService.getSharingKeys();
						});
					});
				};

				$scope.updateSharingKeys = function () {
					$scope.active_vault.private_sharing_key = EncryptService.encryptString(angular.copy($scope.sharing_keys.private_sharing_key));
					$scope.active_vault.public_sharing_key = angular.copy($scope.sharing_keys.public_sharing_key);
					VaultService.updateSharingKeys($scope.active_vault).then(function () {
						$scope.sharing_keys = ShareService.getSharingKeys();
					});
				};

			}]);
}());