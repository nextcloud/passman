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
		.controller('CredentialEditCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'FileService', 'EncryptService', 'TagService', 'NotificationService', 'ShareService', '$translate','$rootScope',
			function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, FileService, EncryptService, TagService, NotificationService, ShareService, $translate, $rootScope) {
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

				$scope.currentTab = {
					title: $translate.instant('general'),
					url: 'views/partials/forms/edit_credential/basics.html',
					color: 'blue'
				};
				$scope.otpType = 'qrcode';
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

				//store password to check if it was changed if this credential has been compromised
				$scope.oldPassword=$scope.storedCredential.password;

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
					_field.secret = (_field.field_type === 'password');
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
         			 $scope.new_custom_field.value = {
            			filename: file.name,
            			size: file.size,
            			mimetype: file.type,
            			data: file.data
          			};
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
					console.log($translate.instant('error.loading.file'), error);
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
					if (!QRCode) {
						NotificationService.showNotification($translate.instant('invalid.qr'), 5000);
						return;
					}
					/** global: URL */
					var uri = new URL(QRCode.qrData);
					var type = (uri.href.indexOf('totp/') !== -1) ? 'totp' : 'hotp';
					var label = uri.pathname.replace('//'+ type +'/', '');
					$scope.storedCredential.otp = {
						type: type,
						label: decodeURIComponent(label),
						qr_uri: QRCode,
						issuer: uri.searchParams.get('issuer'),
						secret: uri.searchParams.get('secret')
					};
					$scope.$digest();
				};

				$scope.saving = false;

				$scope.compromise = function () {
					console.log("This password was compromised");
					$scope.storedCredential.compromised=true;
				};

				$scope.saveCredential = function () {
					$scope.saving = true;

					if($scope.storedCredential.compromised){
						if($scope.oldPassword !== $scope.storedCredential.password){
							$scope.storedCredential.compromised=false;
						}
					}

					if ($scope.new_custom_field.label && $scope.new_custom_field.value) {
						$scope.storedCredential.custom_fields.push(angular.copy($scope.new_custom_field));
					}

					if ($scope.storedCredential.password !== $scope.storedCredential.password_repeat){
						$scope.saving = false;
						NotificationService.showNotification($translate.instant('password.do.not.match'), 5000);
						return;
					}

					//@TODO  validation
					//@TODO When credential is expired and has renew interval set, calc new expire time.
					delete $scope.storedCredential.password_repeat;
					
					if (!$scope.storedCredential.credential_id) {
						$scope.storedCredential.vault_id = $scope.active_vault.vault_id;
						CredentialService.createCredential($scope.storedCredential).then(function (new_cred) {
							$scope.saving = false;
							$location.path('/vault/' + $routeParams.vault_id);
							NotificationService.showNotification($translate.instant('credential.created'), 5000);

                            $scope.updateExistingListWithCredential(new_cred);
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
						CredentialService.updateCredential(_credential, _useKey).then(function (updated_cred) {
							$scope.saving = false;
							SettingsService.setSetting('edit_credential', null);
							$location.path('/vault/' + $routeParams.vault_id);
							NotificationService.showNotification($translate.instant('credential.updated'), 5000);

                            $scope.updateExistingListWithCredential(updated_cred);
						});
					}
                };

                $scope.updateExistingListWithCredential = function (credential) {
                    try {
                        if (!credential.shared_key) {
                            credential = CredentialService.decryptCredential(credential);
                        } else {
                            var enc_key = EncryptService.decryptString(credential.shared_key);
                            credential = ShareService.decryptSharedCredential(credential, enc_key);
                        }
                        credential.tags_raw = credential.tags;

                        var found=false;
                        var credList=$rootScope.vaultCache[$scope.active_vault.guid].credentials;
                        for (var i = 0; i < credList.length; i++) {
			    			if (credList[i].credential_id === credential.credential_id) {
                                $rootScope.vaultCache[$scope.active_vault.guid].credentials[i]=credential;
                                found=true;
			    			}
                        }

                        if(!found){
                            $rootScope.vaultCache[$scope.active_vault.guid].credentials.push(credential);
						}
                        $rootScope.$broadcast('push_decrypted_credential_to_list', credential);

                    } catch (e) {
                        NotificationService.showNotification($translate.instant('error.decrypt'), 5000);
                        console.log(e);
                    }
                };

				$scope.cancel = function () {
					$location.path('/vault/' + $routeParams.vault_id);
				};
			}]);
}());