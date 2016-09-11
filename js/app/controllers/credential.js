'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('CredentialCtrl', ['$scope', 'VaultService', 'SettingsService', '$location', 'CredentialService', '$rootScope', function ($scope, VaultService, SettingsService, $location, CredentialService, $rootScope) {
		$scope.active_vault = VaultService.getActiveVault();
		if(! SettingsService.getSetting('defaultVault') || ! SettingsService.getSetting('defaultVaultPass')){
			if(!$scope.active_vault){
				$location.path('/')
			}
		} else {
			if(SettingsService.getSetting('defaultVault')  && SettingsService.getSetting('defaultVaultPass')){
				var _vault = angular.copy(SettingsService.getSetting('defaultVault'))
				_vault.vaultKey = angular.copy(SettingsService.getSetting('defaultVaultPass'));
				VaultService.setActiveVault(_vault);
				$scope.active_vault = _vault;
			}

		}

		$rootScope.$on('logout', function(){
			console.log('Logout received, clean up');
			$scope.credentials = [];
			$scope.$parent.selectedVault = false;
			$scope.active_vault = null;
		});

		var fetchCredentials = function(){
			VaultService.getVault($scope.active_vault).then(function(credentials) {
				var _credentials = [];
				for(var i = 0; i < credentials.length; i++){
					var credential = CredentialService.decryptCredential(angular.copy(credentials[i]));
					_credentials.push(credential);
				}
				$scope.credentials = _credentials;
			});
		};

		if($scope.active_vault){
			$scope.$parent.selectedVault = true;
			fetchCredentials();
		}
	}]);
