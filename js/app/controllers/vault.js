'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('VaultCtrl', ['$scope', 'VaultService', 'SettingsService', 'CredentialService', function ($scope, VaultService, SettingsService, CredentialService) {
		VaultService.getVaults().then(function (vaults) {
			$scope.vaults = vaults;
			if(SettingsService.getSetting('defaultVault') != null){
				var default_vault = SettingsService.getSetting('defaultVault');

				/**
				 * Using a native for loop for preformance reasons.
				 * More info see http://stackoverflow.com/questions/13843972/angular-js-break-foreach
				 */
				for(var i = 0; i < vaults.length; i++){
					var vault = vaults[i];
					if(vault.guid == default_vault.guid){
						$scope.default_vault = true;
						$scope.list_selected_vault = SettingsService.getSetting('defaultVault');
						break;
					}
				}
			}
		});


		$scope.default_vault = false;
		$scope.remember_vault_password = false;
		$scope.list_selected_vault = false;

		$scope.toggleDefaultVault = function(){
			$scope.default_vault = !$scope.default_vault;
			if($scope.default_vault == true){
				SettingsService.setSetting('defaultVault', $scope.list_selected_vault);
			} else {
				SettingsService.setSetting('defaultVault', null);
			}
		};

		$scope.clearState = function () {
			$scope.list_selected_vault = false;
			$scope.creating_vault = false;
		};

		$scope.selectVault = function(vault){
			$scope.list_selected_vault = vault;
		};

		$scope.newVault = function(){
			$scope.creating_vault = true;
		};


		$scope.vaultDecryptionKey = '';
		$scope.loginToVault = function (vault) {
			VaultService.getVault(vault).then(function(credentials){
				for(var i = 0; i < credentials.length; i++){
					var credential = credentials[i];
					if(credential.hidden = true){
						console.log(credential);
						break;
					}
				}
			})
		};

		$scope.vaultKey = '';
		$scope.vaultKey_2 = '';
		$scope.createVault = function(vault_name){
			if($scope.vaultKey != $scope.vaultKey_2){
				//@todo Show an message
				return;
			}
			VaultService.createVault(vault_name).then(function (vault) {
				$scope.vaults.push(vault)
				var _vault = angular.copy(vault)
				_vault.vaultKey = angular.copy($scope.vaultKey);
				VaultService.setActiveVault(_vault);
				var test_credential = CredentialService.newCredential();
				test_credential.label = 'Test key for vault '+ vault_name;
				test_credential.hidden = true;
				test_credential.vault_id = vault.vault_id;
				test_credential.password = 'lorum ipsum';
				CredentialService.createCredential(test_credential).then(function (result) {
					console.log('succes =)')
					console.log(result)
					//@TODO Redirect to newly created vault
				})
			});
		};
	}]);
