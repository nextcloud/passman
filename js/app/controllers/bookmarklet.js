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
		.controller('BookmarkletCtrl', ['$scope', '$rootScope', '$location', 'VaultService', 'CredentialService', 'SettingsService', 'NotificationService', 'EncryptService', 'TagService', 'FileService',
			function ($scope, $rootScope, $location, VaultService, CredentialService, SettingsService, NotificationService, EncryptService, TagService, FileService) {
				$scope.active_vault = false;

				$scope.http_warning_hidden = true;
				if ($location.$$protocol === 'http') {
					$scope.using_http = true;
					//$scope.http_warning_hidden = false;

				}

				$scope.logout = function () {
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
					var _vault = {};
					var key_size = 1024;
					ShareService.generateRSAKeys(key_size).progress(function (progress) {
						var p = progress > 0 ? 2 : 1;
						$scope.creating_keys = 'Generating sharing keys (' + p + ' / 2)';
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
						var c = EncryptService.decryptString(vault.challenge_password);
						if ($scope.remember_vault_password) {
							SettingsService.setSetting('defaultVaultPass', vault_key);
						}
						_loginToVault(vault, vault_key);

					} catch (e) {
						$scope.error = 'Incorrect vault password!';
					}

				};


				$scope.createVault = function (vault_name, vault_key, vault_key2) {
					if (vault_key !== vault_key2) {
						$scope.error = 'Passwords do not match';
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
						CredentialService.createCredential(test_credential).then(function (result) {
							_vault.public_sharing_key = angular.copy($scope.sharing_keys.public_sharing_key);
							_vault.private_sharing_key = EncryptService.encryptString(angular.copy($scope.sharing_keys.private_sharing_key));
							VaultService.updateSharingKeys(_vault).then(function (result) {
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

				$scope.currentTab = {
					title: 'General',
					url: 'views/partials/forms/edit_credential/basics.html',
					color: 'blue'
				};

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
					secret: false
				};
				$scope.new_custom_field = angular.copy(_customField);

				$scope.addCustomField = function () {
					if (!$scope.new_custom_field.label) {
						NotificationService.showNotification('Please fill in a label', 3000);
					}
					if (!$scope.new_custom_field.value) {
						NotificationService.showNotification('Please fill in a value!', 3000);
					}
					if (!$scope.new_custom_field.label || !$scope.new_custom_field.value) {
						return;
					}
					$scope.storedCredential.custom_fields.push(angular.copy($scope.new_custom_field));
					$scope.new_custom_field = angular.copy(_customField);
				};

				$scope.deleteCustomField = function (field) {
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

				$scope.fileLoadError = function (error, file) {
					console.log(error, file);
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
					//@TODO  validation
					delete $scope.storedCredential.password_repeat;
					if (!$scope.storedCredential.credential_id) {
						$scope.storedCredential.vault_id = $scope.active_vault.vault_id;

						CredentialService.createCredential($scope.storedCredential).then(function (result) {
							NotificationService.showNotification('Credential created!', 5000);
						});
					}
				};
				console.log('test')
			}
		]);

}());