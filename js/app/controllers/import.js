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
		.controller('ImportCtrl', ['$scope', '$window', 'CredentialService', 'VaultService', '$translate', function ($scope, $window, CredentialService, VaultService, $translate) {
			$scope.available_importers = [];
			$scope.active_vault = VaultService.getActiveVault();


			$scope.$watch(function () {
				return $window.PassmanImporter;
			}, function (importers) {
				for (var key in importers) {
					var importer = importers[key];
					if (importer.hasOwnProperty('info')) {
						$scope.available_importers.push(importer.info);
					}
				}
			}, true);
			$scope.log = [];
			$scope.setImporter = function (importer) {
				importer = JSON.parse(importer);
				$scope.selectedImporter = importer;
			};
			var _log = function (str) {
				$scope.log.push(str);
			};

			var file_data;
			$scope.fileLoaded = function (file) {
				file_data = file.data.split(',');
				file_data = decodeURIComponent(escape(window.atob(file_data[1]))); //window.atob();
				_log($translate.instant('import.file.read'));
				$scope.$digest();
			};

			$scope.fileLoadError = function (file) {
				console.error($translate.instant('error.loading.file'), file);
			};
			$scope.fileSelectProgress = function () {

			};

			var parsed_data;

			$scope.import_progress = {
				progress: 0,
				loaded: 0,
				total: 0
			};
			var addCredential = function (parsed_data_index) {
				if (!parsed_data[parsed_data_index]) {
					return;
				}
				var _credential = parsed_data[parsed_data_index];
				if (!_credential.label) {
					if (parsed_data[parsed_data_index + 1]) {
						_log($translate.instant('import.no.label'));
						addCredential(parsed_data_index + 1);
					}
					return;
				}
				_log($translate.instant('import.adding', {credential: _credential.label }));
				_credential.vault_id = $scope.active_vault.vault_id;
				CredentialService.createCredential(_credential).then(function (result) {
					if (result.credential_id) {
						_log($translate.instant('import.added', {credential: _credential.label }));
						if (parsed_data[parsed_data_index + 1]) {
							$scope.import_progress = {
								progress: parsed_data_index / parsed_data.length * 100,
								loaded: parsed_data_index,
								total: parsed_data.length
							};

							addCredential(parsed_data_index + 1);
						} else {
							$scope.import_progress = {
								progress: 100,
								loaded: parsed_data.length,
								total: parsed_data.length
							};
							_log($translate.instant('done'));
						}
					}
				});
			};


			$scope.file_read_progress = {
				percent: 0,
				loaded: 0,
				total: 0
			};
			$scope.startImport = function () {
				$scope.import_progress = 0;
				$scope.file_read_percent = 0;
				if (file_data) {
					$window.PassmanImporter[$scope.selectedImporter.id]
						.readFile(file_data)
						.then(function (parseddata) {
							parsed_data = parseddata;
							$scope.file_read_progress = {
								percent: 100,
								loaded: parsed_data.length,
								total: parsed_data.length
							};
							var msg = $translate.instant('import.loaded').replace('{{num}}', parsed_data.length);
							_log(msg);
							if (parsed_data.length > 0) {
								addCredential(0);
							} else {
								// @TODO Show message no data found
							}
						}).progress(function (progress) {
						$scope.file_read_progress = progress;
						$scope.$digest();
					});
				}
			};

		}]);

}());