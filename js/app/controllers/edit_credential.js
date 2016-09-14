'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('CredentialEditCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'FileService', 'EncryptService', function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, FileService, EncryptService) {
		$scope.active_vault = VaultService.getActiveVault();


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

		$scope.pwSettings = {
			'length': 12,
			'useUppercase': true,
			'useLowercase': true,
			'useDigits': true,
			'useSpecialChars': true,
			'minimumDigitCount': 3,
			'avoidAmbiguousCharacters': false,
			'requireEveryCharType': true
		};

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
			$location.path('/vault/' + $routeParams.vault_id);
		} else {
			$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
			$scope.storedCredential.password_repeat = angular.copy($scope.storedCredential.password);
		}


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
				//@TODO move OC.Notification to a service
				OC.Notification.showTemporary('Please fill in a label');
			}
			if (!$scope.new_custom_field.value) {
				//@TODO move OC.Notification to a service
				OC.Notification.showTemporary('Please fill in a value!');
			}
			if (!$scope.new_custom_field.label || !$scope.new_custom_field.value) {
				return;
			}
			$scope.storedCredential.custom_fields.push(angular.copy($scope.new_custom_field));
			$scope.new_custom_field = angular.copy(_customField);
		};

		$scope.deleteCustomField = function(field){
			var idx = $scope.storedCredential.custom_fields.indexOf(field);
			$scope.storedCredential.custom_fields.splice(idx, 1);
		};

		$scope.new_file = {
			name: '',
			data: null
		};

		$scope.deleteFile = function(file){
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
		//@TODO Set tags, read them from tag service

		$scope.parseQR = function(QRCode){
			var re = /otpauth:\/\/(totp|hotp)\/(.*)\?(secret|issuer)=(.*)&(issuer|secret)=(.*)/, parsedQR,qrInfo;
			parsedQR = (QRCode.qrData.match(re));
			if(parsedQR)
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
			delete $scope.storedCredential.password_repeat;
			if(!$scope.storedCredential.credential_id){
				$scope.storedCredential.vault_id = $scope.active_vault.vault_id;
				CredentialService.createCredential($scope.storedCredential).then(function (result) {
					$location.path('/vault/' + $routeParams.vault_id);
					//@TODO Show notification
				})
			} else {
				CredentialService.updateCredential($scope.storedCredential).then(function (result) {
					SettingsService.setSetting('edit_credential', null);
					$location.path('/vault/' + $routeParams.vault_id);
					//@TODO Show notification
				})
			}
		};

		$scope.cancel = function(){
			$location.path('/vault/' + $routeParams.vault_id);
		}
	}]);
