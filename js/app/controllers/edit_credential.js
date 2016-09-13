'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('CredentialEditCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams) {
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
			$location.path('/vault/'+ $routeParams.vault_id);
		} else {
			$scope.storedCredential = storedCredential;
			$scope.storedCredential.password_repeat = angular.copy(storedCredential.password);
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

		$scope.pwGenerated = function(pass){
			$scope.storedCredential.password_repeat = pass;
		};

		var _customField = {
			label: '',
			value: '',
			secret: false
		};
		$scope.new_custom_field = angular.copy(_customField);

		$scope.addCustomField = function(){
			if(!$scope.new_custom_field.label){
				//@TODO move OC.Notification to a service
				OC.Notification.showTemporary('Please fill in a label');
			}
			if(!$scope.new_custom_field.value){
				//@TODO move OC.Notification to a service
				OC.Notification.showTemporary('Please fill in a value!');
			}
			if(!$scope.new_custom_field.label || !$scope.new_custom_field.value){
				return;
			}
			$scope.storedCredential.custom_fields.push(angular.copy($scope.new_custom_field));
			$scope.new_custom_field = angular.copy(_customField);
		}
	}]);
