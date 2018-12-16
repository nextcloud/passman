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
		.controller('BookmarkletCtrl', ['$scope', '$rootScope', '$location', 'VaultService', 'CredentialService', 'SettingsService', 'NotificationService', 'EncryptService', 'TagService', 'FileService', 'ShareService', '$translate',
			function ($scope, $rootScope, $location, VaultService, CredentialService, SettingsService, NotificationService, EncryptService, TagService, FileService, ShareService, $translate) {
				$scope.active_vault = false;

				$scope.http_warning_hidden = true;
				if ($location.$$protocol === 'http') {
					$scope.using_http = true;
					//$scope.http_warning_hidden = false;

				}

				$scope.logout = function () {
                    //see vault.js:54
                    $rootScope.override_default_vault=true;
					$scope.active_vault = false;
				};
				if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
					var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
					VaultService.getVault(_vault).then(function (vault) {
						vault.vaultKey = angular.copy(SettingsService.getSetting('defaultVaultPass'));
						VaultService.setActiveVault(vault);
						$scope.active_vault = vault;

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
				}
				/**
				 * Vault selection stuff
				 */
				VaultService.getVaults().then(function (vaults) {
					$scope.vaults = vaults;

				});
				$scope.default_vault = false;
				$scope.remember_vault_password = false;
				$scope.list_selected_vault = false;


				$scope.toggleDefaultVault = function () {
					$scope.default_vault = !$scope.default_vault;
					if ($scope.default_vault === true) {
						SettingsService.setSetting('defaultVault', $scope.list_selected_vault);
					} else {
						SettingsService.setSetting('defaultVault', null);
					}
				};

				$scope.toggleRememberPassword = function () {
					$scope.remember_vault_password = !$scope.remember_vault_password;
					if ($scope.remember_vault_password) {
						SettingsService.setSetting('defaultVault', $scope.list_selected_vault);
						$scope.default_vault = true;
					}
					if ($scope.remember_vault_password !== true) {
						SettingsService.setSetting('defaultVault', null);
					}
				};

				$scope.clearState = function () {
					$scope.list_selected_vault = false;
					$scope.creating_vault = false;
					$scope.error = false;
				};

				$scope.selectVault = function (vault) {
					$scope.list_selected_vault = vault;
				};
				$scope.sharing_keys = {};
				$scope.newVault = function () {
					$scope.creating_vault = true;
					var key_size = 1024;
					ShareService.generateRSAKeys(key_size).progress(function (progress) {
						var p = progress > 0 ? 2 : 1;
						var msg =  $translate.instant('generating.sharing.keys');
						msg = msg.replace('%step', p);
						$scope.creating_keys = msg;
						$scope.$digest();
					}).then(function (kp) {
						var pem = ShareService.rsaKeyPairToPEM(kp);
						$scope.creating_keys = false;
						$scope.sharing_keys.private_sharing_key = pem.privateKey;
						$scope.sharing_keys.public_sharing_key = pem.publicKey;
						$scope.$digest();
					});

				};

				var _loginToVault = function (vault, vault_key) {
					var _vault = angular.copy(vault);
					_vault.vaultKey = angular.copy(vault_key);
					delete _vault.credentials;
					$scope.active_vault = _vault;

				};

				$scope.vaultDecryptionKey = '';
				$scope.loginToVault = function (vault, vault_key) {
					$scope.error = false;
					var _vault = angular.copy(vault);
					_vault.vaultKey = angular.copy(vault_key);

					VaultService.setActiveVault(_vault);
					try {
						EncryptService.decryptString(vault.challenge_password);
						if ($scope.remember_vault_password) {
							SettingsService.setSetting('defaultVaultPass', vault_key);
						}
						_loginToVault(vault, vault_key);

					} catch (e) {
						$scope.error = $translate.instant('invalid.vault.key');
					}

				};


				$scope.createVault = function (vault_name, vault_key, vault_key2) {
					if (vault_key !== vault_key2) {
						$scope.error = $translate.instant('password.do.not.match');
						return;
					}
					VaultService.createVault(vault_name).then(function (vault) {
						$scope.vaults.push(vault);
						var _vault = angular.copy(vault);
						_vault.vaultKey = angular.copy(vault_key);
						VaultService.setActiveVault(_vault);
						var test_credential = CredentialService.newCredential();
						test_credential.label = 'Test key for vault ' + vault_name;
						test_credential.hidden = true;
						test_credential.vault_id = vault.vault_id;
						test_credential.password = 'lorum ipsum';
						CredentialService.createCredential(test_credential).then(function () {
							_vault.public_sharing_key = angular.copy($scope.sharing_keys.public_sharing_key);
							_vault.private_sharing_key = EncryptService.encryptString(angular.copy($scope.sharing_keys.private_sharing_key));
							VaultService.updateSharingKeys(_vault).then(function () {
								_loginToVault(vault, vault_key);
							});
						});
					});
				};


				/**
				 * End vault selection stiff
				 */
				$scope.storedCredential = CredentialService.newCredential();

				var QueryString = function () {
					// This function is anonymous, is executed immediately and
					// the return value is assigned to QueryString!
					var query_string = {};
					var query = window.location.search.substring(1);
					var vars = query.split("&");
					for (var i = 0; i < vars.length; i++) {
						var pair = vars[i].split("=");
						// If first entry with this name
						if (typeof query_string[pair[0]] === "undefined") {
							query_string[pair[0]] = decodeURIComponent(pair[1]);
							// If second entry with this name
						} else if (typeof query_string[pair[0]] === "string") {
							var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
							query_string[pair[0]] = arr;
							// If third or later entry with this name
						} else {
							query_string[pair[0]].push(decodeURIComponent(pair[1]));
						}
					}
					return query_string;
				}();
				var query_string = QueryString;
				$scope.storedCredential.label = query_string.title;
				$scope.storedCredential.url = query_string.url;

				$scope.setHttpWarning = function (state) {
					$scope.http_warning_hidden = state;
				};




				$translate(['general', 'password', 'custom.fields','files','otp']).then(function (translations) {
					$scope.tabs = [{
						title: translations.general,
						url: 'views/partials/forms/edit_credential/basics.html',
						color: 'blue'
					}, {
						title: translations.password,
						url: 'views/partials/forms/edit_credential/password.html',
						color: 'green'
					}, {
						title:translations['custom.fields'],
						url: 'views/partials/forms/edit_credential/custom_fields.html',
						color: 'orange'
					}, {
						title: translations.files,
						url: 'views/partials/forms/edit_credential/files.html',
						color: 'yellow'
					}, {
						title: translations.otp,
						url: 'views/partials/forms/edit_credential/otp.html',
						color: 'purple'
					}];
					$scope.currentTab = $scope.tabs[0];
				});

				$scope.getTags = function ($query) {
					return TagService.searchTag($query);
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
						NotificationService.showNotification($translate.instant('error.no.label'), 3000);
					}
					if (!_field.value) {
						NotificationService.showNotification($translate.instant('error.no.value'), 3000);
					}
					if (!_field.label || !_field.value) {
						return;
					}
					$scope.selected_field_type = 'text';

					_field.secret = angular.copy(($scope.selected_field_type === 'password'));
					_field.field_type =  angular.copy($scope.selected_field_type);
					if(_field.field_type === 'file'){
						var _file = $scope.new_custom_field.value;
						FileService.uploadFile(_file).then(function (result) {
							delete result.file_data;
							result.filename = EncryptService.decryptString(result.filename);
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
					var _file = {
						filename: file.name,
						size: file.size,
						mimetype: file.type,
						data: file.data
					};
					FileService.uploadFile(_file).then(function (result) {
						delete result.file_data;
						result.filename = EncryptService.decryptString(result.filename);
						$scope.storedCredential.files.push(result);
					});


					$scope.$digest();
				};

				$scope.fileLoadError = function (error) {
					return error;
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
					qrInfo = [];
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
					//@TODO  validation
					delete $scope.storedCredential.password_repeat;
					if (!$scope.storedCredential.credential_id) {
						$scope.storedCredential.vault_id = $scope.active_vault.vault_id;

						CredentialService.createCredential($scope.storedCredential).then(function () {
							NotificationService.showNotification($translate.instant('credential.created'), 5000);
						});
					}
				};

			}
		]);

}());