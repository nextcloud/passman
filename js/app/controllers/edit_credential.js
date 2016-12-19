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
		.controller('CredentialEditCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'FileService', 'EncryptService', 'TagService', 'NotificationService', 'ShareService',
			function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, FileService, EncryptService, TagService, NotificationService, ShareService) {
				$scope.active_vault = VaultService.getActiveVault();
				if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
					if (!$scope.active_vault) {
						$location.path('/');
						return;
					}
				} else {
					if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
						var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
						_vault.vaultKey = SettingsService.getSetting('defaultVaultPass');
						VaultService.setActiveVault(_vault);
						$scope.active_vault = _vault;
					}
				}

				VaultService.getVault($scope.active_vault).then(function (vault) {
					vault.vaultKey = VaultService.getActiveVault().vaultKey;
					delete vault.credentials;
					VaultService.setActiveVault(vault);
					$scope.pwSettings = VaultService.getVaultSetting('pwSettings',
						{
							'length': 12,
							'useUppercase': true,
							'useLowercase': true,
							'useDigits': true,
							'useSpecialChars': true,
							'minimumDigitCount': 3,
							'avoidAmbiguousCharacters': false,
							'requireEveryCharType': true,
							'generateOnCreate': true
						});
				});

				$scope.tabs = [{
					title: 'General',
					url: 'views/partials/forms/edit_credential/basics.html',
					color: 'blue'
				}, {
					title: 'Password',
					url: 'views/partials/forms/edit_credential/password.html',
					color: 'green'
				}, {
					title: 'Custom fields',
					url: 'views/partials/forms/edit_credential/custom_fields.html',
					color: 'orange'
				}, {
					title: 'Files',
					url: 'views/partials/forms/edit_credential/files.html',
					color: 'yellow'
				}, {
					title: 'OTP',
					url: 'views/partials/forms/edit_credential/otp.html',
					color: 'purple'
				}];


				if ($scope.active_vault) {
					$scope.$parent.selectedVault = true;
				}
				var storedCredential = SettingsService.getSetting('edit_credential');

				if (!storedCredential) {
					CredentialService.getCredential($routeParams.credential_id).then(function (result) {
						$scope.storedCredential = CredentialService.decryptCredential(angular.copy(result));
					});
				} else {
					$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
					$scope.storedCredential.password_repeat = angular.copy($scope.storedCredential.password);
					$scope.storedCredential.expire_time = $scope.storedCredential.expire_time * 1000;
				}

				$scope.getTags = function ($query) {
					return TagService.searchTag($query);
				};

				$scope.currentTab = {
					title: 'General',
					url: 'views/partials/forms/edit_credential/basics.html',
					color: 'blue'
				};

				$scope.onClickTab = function (tab) {
					$scope.currentTab = tab;
				};

				$scope.isActiveTab = function (tab) {
					return tab.url === $scope.currentTab.url;
				};

				/**
				 * Below general edit functions
				 */

				$scope.pwGenerated = function (pass) {
					$scope.storedCredential.password_repeat = pass;
				};


				var _customField = {
					label: '',
					value: '',
					secret: false,
					field_type: 'text'
				};
				$scope.selected_field_type = 'text';
				$scope.new_custom_field = angular.copy(_customField);

				$scope.addCustomField = function () {
					var _field = angular.copy($scope.new_custom_field);

					if (!_field.label) {
						NotificationService.showNotification('Please fill in a label', 3000);
					}
					if (!_field.value) {
						NotificationService.showNotification('Please fill in a value!', 3000);
					}
					if (!_field.label || !_field.value) {
						return;
					}
					$scope.selected_field_type = 'text';

					_field.secret = angular.copy(($scope.selected_field_type === 'password'));
					_field.field_type =  angular.copy($scope.selected_field_type);
					if(_field.field_type === 'file'){
						var key = false;
						var _file = $scope.new_custom_field.value;
						if (!$scope.storedCredential.hasOwnProperty('acl') && $scope.storedCredential.hasOwnProperty('shared_key')) {

							if ($scope.storedCredential.shared_key) {
								key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
							}
						}

						if ($scope.storedCredential.hasOwnProperty('acl')) {
							key = EncryptService.decryptString(angular.copy($scope.storedCredential.acl.shared_key));
						}

						FileService.uploadFile(_file, key).then(function (result) {
							delete result.file_data;
							result.filename = EncryptService.decryptString(result.filename, key);
							_field.value = result;
							$scope.storedCredential.custom_fields.push(_field);
							$scope.new_custom_field = angular.copy(_customField);
						});
					} else {
						$scope.storedCredential.custom_fields.push(_field);
						$scope.new_custom_field = angular.copy(_customField);
					}

				};

				$scope.addFileToCustomField = function (file) {
					var _file = {
						filename: file.name,
						size: file.size,
						mimetype: file.type,
						data: file.data
					};
					$scope.new_custom_field.value = _file;
					$scope.$digest();
				};

				$scope.deleteCustomField = function (field) {
					if(field.hasOwnProperty('field_type')) {
						if (field.field_type === 'file') {
							FileService.deleteFile(field.value);
						}
					}
					var idx = $scope.storedCredential.custom_fields.indexOf(field);
					$scope.storedCredential.custom_fields.splice(idx, 1);
				};

				$scope.new_file = {
					name: '',
					data: null
				};

				$scope.deleteFile = function (file) {
					var idx = $scope.storedCredential.files.indexOf(file);
					FileService.deleteFile(file).then(function () {
						$scope.storedCredential.files.splice(idx, 1);
					});
				};

				$scope.fileLoaded = function (file) {
					var key;
					var _file = {
						filename: file.name,
						size: file.size,
						mimetype: file.type,
						data: file.data
					};

					if (!$scope.storedCredential.hasOwnProperty('acl') && $scope.storedCredential.hasOwnProperty('shared_key')) {

						if ($scope.storedCredential.shared_key) {
							key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
						}
					}

					if ($scope.storedCredential.hasOwnProperty('acl')) {
						key = EncryptService.decryptString(angular.copy($scope.storedCredential.acl.shared_key));
					}


					FileService.uploadFile(_file, key).then(function (result) {
						delete result.file_data;
						result.filename = EncryptService.decryptString(result.filename, key);
						$scope.storedCredential.files.push(result);
					});


					$scope.$digest();
				};

				$scope.fileLoadError = function (error) {
					console.log('Error loading file', error);
				};

				$scope.selected_file = '';
				$scope.fileprogress = [];
				$scope.fileSelectProgress = function (progress) {
					if (progress) {
						$scope.fileprogress = progress;
						$scope.$digest();

					}
				};
				$scope.renewIntervalValue = 0;
				$scope.renewIntervalModifier = '0';

				$scope.updateInterval = function (renewIntervalValue, renewIntervalModifier) {
					var value = parseInt(renewIntervalValue);
					var modifier = parseInt(renewIntervalModifier);
					if (value && modifier) {
						$scope.storedCredential.renew_interval = value * modifier;
					}
				};

				$scope.parseQR = function (QRCode) {
					var re = /otpauth:\/\/(totp|hotp)\/(.*)\?(secret|issuer)=(.*)&(issuer|secret)=(.*)/, parsedQR, qrInfo;
					parsedQR = (QRCode.qrData.match(re));
					if (parsedQR)
						qrInfo = {
							type: parsedQR[1],
							label: decodeURIComponent(parsedQR[2]),
							qr_uri: QRCode
						};
					qrInfo[parsedQR[3]] = parsedQR[4];
					qrInfo[parsedQR[5]] = parsedQR[6];
					$scope.storedCredential.otp = qrInfo;
					$scope.$digest();
				};

				$scope.saveCredential = function () {


					if ($scope.new_custom_field.label && $scope.new_custom_field.value) {
						$scope.storedCredential.custom_fields.push(angular.copy($scope.new_custom_field));
					}


					//@TODO  validation
					//@TODO When credential is expired and has renew interval set, calc new expire time.
					delete $scope.storedCredential.password_repeat;
					
					if (!$scope.storedCredential.credential_id) {
						$scope.storedCredential.vault_id = $scope.active_vault.vault_id;
						CredentialService.createCredential($scope.storedCredential).then(function () {
							$location.path('/vault/' + $routeParams.vault_id);
							NotificationService.showNotification('Credential created!', 5000);
						});
					} else {

						var key, _credential;
						if (!$scope.storedCredential.hasOwnProperty('acl') && $scope.storedCredential.hasOwnProperty('shared_key')) {

							if ($scope.storedCredential.shared_key) {
								key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
							}
						}

						if ($scope.storedCredential.hasOwnProperty('acl')) {
							key = EncryptService.decryptString(angular.copy($scope.storedCredential.acl.shared_key));
						}

						if (key) {
							_credential = ShareService.encryptSharedCredential($scope.storedCredential, key);
						} else {
							_credential = angular.copy($scope.storedCredential);
						}

						delete _credential.shared_key;
						var _useKey = (key != null);
						var regex = /(<([^>]+)>)/ig;
						if(_credential.description && _credential.description !== "") {
							_credential.description = _credential.description.replace(regex, "");
						}
						CredentialService.updateCredential(_credential, _useKey).then(function () {
							SettingsService.setSetting('edit_credential', null);
							$location.path('/vault/' + $routeParams.vault_id);
							NotificationService.showNotification('Credential updated!', 5000);
						});
					}

				};

				$scope.cancel = function () {
					$location.path('/vault/' + $routeParams.vault_id);
				};
			}]);
}());