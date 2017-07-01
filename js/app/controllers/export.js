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
	 * @name passmanApp.controller:ImportCtrl
	 * @description
	 * # ImportCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('ExportCtrl', ['$scope', '$window', 'CredentialService', 'VaultService', 'FileService', 'EncryptService', '$translate', function ($scope, $window, CredentialService, VaultService, FileService, EncryptService, $translate) {
			$scope.available_exporters = [];
			$scope.active_vault = VaultService.getActiveVault();
			$scope.confirm_key = '';

			$scope.$watch(function () {
				return $window.PassmanExporter;
			}, function (exporters) {
				exporters = Object.keys(angular.copy(exporters));
				for (var i = 0; i < exporters.length; i++) {
					var exporter = exporters[i];
					if ($window.PassmanExporter[exporter].hasOwnProperty('info')) {
						$scope.available_exporters.push($window.PassmanExporter[exporter].info);
					}
				}
			}, true);
			$scope.log = [];
			$scope.setExporter = function (exporter) {
				exporter = JSON.parse(exporter);
				$scope.selectedExporter = exporter;
			};
			var _log = function (str) {
				$scope.log.push(str);
			};


			$scope.startExport = function () {
				$scope.error = false;
				if(VaultService.getActiveVault().vaultKey !== $scope.confirm_key){
				var msg = $translate.instant('invalid.vault.key');
					$scope.error = msg;
					_log(msg);
					return;
				}
				_log($translate.instant('export.starting'));
				var _credentials = [];
				VaultService.getVault(VaultService.getActiveVault()).then(function (vault) {
					_log($translate.instant('export.decrypt'));
					if (vault.hasOwnProperty('credentials')) {
						if (vault.credentials.length > 0) {
							for (var i = 0; i < vault.credentials.length; i++) {
								var _credential = angular.copy(vault.credentials[i]);
								if (_credential.hidden === 0) {
									var key = CredentialService.getSharedKeyFromCredential(_credential);
									_credential = CredentialService.decryptCredential(_credential, key);
									_credential.vault_key = key;
									_credentials.push(_credential);
								}
							}
							$window.PassmanExporter[$scope.selectedExporter.id].export(_credentials, FileService, EncryptService).then(function () {
								_log($translate.instant('done'));
							});
						}

					}
				});
			};


		}]);

}());