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
		.controller('ImportCtrl', ['$scope', '$rootScope', '$window', 'CredentialService', 'VaultService', 'FileService', 'EncryptService', '$translate', function ($scope, $rootScope, $window, CredentialService, VaultService, FileService, EncryptService, $translate) {
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

			/**
			 * Needed to remove the custom _history property from the credential object before sending it to the server.
			 */
			var toImportPayload = function (credential) {
				var payload = angular.copy(credential);
				delete payload._history;
				payload.vault_id = $scope.active_vault.vault_id;
				return payload;
			};

			/**
			 * Called after importCredentialWithHistory to log the import and call addCredential again for the next credential.
			 */
			var finishCredential = function (parsed_data_index, label, revisionCount) {
				if (revisionCount) {
					_log($translate.instant('import.added.revisions', {
						credential: label,
						count: revisionCount
					}));
				} else {
					_log($translate.instant('import.added', {credential: label}));
				}
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
					$rootScope.refresh();
				}
			};

			/**
			 * Import the given credential (with its history if _history property is present) into the server.
			 * @param {Object} _credential The credential to import.
			 * @returns {Promise} A promise that resolves to the imported credential.
			 */
			var importCredentialWithHistory = function (_credential) {
				var history = Array.isArray(_credential._history) ? _credential._history : [];
				var versions = history.concat([_credential]);
				var first = toImportPayload(versions[0]);
				if (!first.label) {
					first.label = _credential.label;
				}

				return CredentialService.createCredential(first).then(function (created) {
					if (!created.credential_id) {
						return created;
					}

					var applyVersion = function (index, previous) {
						if (index >= versions.length) {
							return previous;
						}
						var next = toImportPayload(versions[index]);
						next.guid = previous.guid;
						next.credential_id = previous.credential_id;
						if (!next.label) {
							next.label = _credential.label;
						}
						return CredentialService.updateCredential(next).then(function (updated) {
							return applyVersion(index + 1, updated);
						});
					};

					if (versions.length === 1) {
						return created;
					}
					return applyVersion(1, created);
				});
			};

			/**
			 * Recursive function chain, starts with index 0 and will be called again after importCredentialWithHistory -> finishCredential -> addCredential.
			 * The abort condition if part of finishCredential.
			 */
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
				var revisionCount = Array.isArray(_credential._history) ? _credential._history.length : 0;
				if (revisionCount) {
					_log($translate.instant('import.adding.revisions', {
						credential: _credential.label,
						count: revisionCount
					}));
				} else {
					_log($translate.instant('import.adding', {credential: _credential.label }));
				}
				importCredentialWithHistory(_credential).then(function (result) {
					if (result && result.credential_id) {
						finishCredential(parsed_data_index, _credential.label, revisionCount);
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
					var process = $window.PassmanImporter[$scope.selectedImporter.id];

					if (process && typeof process.setRequiredServices === 'function') {
						process.setRequiredServices(FileService, EncryptService);
					}

					process.readFile(file_data).then(function (parseddata) {
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
					}).error(function (err) {
						_log($translate.instant('import.parse.error'));
						console.error(err);
						$scope.$digest();
					});
				}
			};

		}]);

}());
