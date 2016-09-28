'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('CredentialEditCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'FileService', 'EncryptService', 'TagService', 'NotificationService',
		function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, FileService, EncryptService, TagService, NotificationService) {
			$scope.active_vault = VaultService.getActiveVault();
			if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
				if (!$scope.active_vault) {
					$location.path('/')
				}
			} else {
				if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
					var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
					VaultService.getVault(_vault).then(function (vault) {
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
								'generateOnCreate': true,
							})
					})
				}
			}

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



			if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
				if (!$scope.active_vault) {
					$location.path('/')
				}
			} else {
				if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
					var _vault = angular.copy(SettingsService.getSetting('defaultVault'))
					_vault.vaultKey = angular.copy(SettingsService.getSetting('defaultVaultPass'));
					VaultService.setActiveVault(_vault);
					$scope.active_vault = _vault;

				}
			}
			if ($scope.active_vault) {
				$scope.$parent.selectedVault = true;
			}
			var storedCredential = SettingsService.getSetting('edit_credential');

			if (!storedCredential) {
				CredentialService.getCredential($routeParams.credential_id).then(function(result){
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
				return tab.url == $scope.currentTab.url;
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


				$scope.$apply()
			};

			$scope.fileLoadError = function (error, file) {
				console.log(error, file)
			};

			$scope.selected_file = '';
			$scope.fileprogress = [];
			$scope.fileSelectProgress = function (progress) {
				if (progress) {
					$scope.fileprogress = progress;
					$scope.$apply()

				}
			};
			$scope.renewIntervalValue = 0;
			$scope.renewIntervalModifier = '0';

			$scope.updateInterval = function(renewIntervalValue, renewIntervalModifier){
				var value = parseInt(renewIntervalValue);
				var modifier = parseInt(renewIntervalModifier);
				if( value && modifier) {
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
				$scope.$apply()
			};

			$scope.saveCredential = function () {
				//@TODO  validation
				//@TODO When credential is expired and has renew interval set, calc new expire time.

				delete $scope.storedCredential.password_repeat;
				if (!$scope.storedCredential.credential_id) {
					$scope.storedCredential.vault_id = $scope.active_vault.vault_id;
					CredentialService.createCredential($scope.storedCredential).then(function (result) {
						$location.path('/vault/' + $routeParams.vault_id);
						NotificationService.showNotification('Credential created!', 5000)
					})
				} else {
					CredentialService.updateCredential($scope.storedCredential).then(function (result) {
						SettingsService.setSetting('edit_credential', null);
						$location.path('/vault/' + $routeParams.vault_id);
						NotificationService.showNotification('Credential updated!', 5000)
					})
				}
			};

			$scope.cancel = function () {
				$location.path('/vault/' + $routeParams.vault_id);
			}
		}]);
