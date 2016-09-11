'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('VaultCtrl', ['$scope', 'VaultService', 'SettingsService', 'CredentialService', '$location', function ($scope, VaultService, SettingsService, CredentialService, $location) {
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
						if(SettingsService.getSetting('defaultVaultPass') != ''){
							$location.path('/vault/'+ vault.vault_id);
						}
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

		$scope.toggleRememberPassword = function(){
			$scope.remember_vault_password = !$scope.remember_vault_password;
			if($scope.remember_vault_password != true){
				SettingsService.setSetting('defaultVault', null);
			}
		};

		$scope.clearState = function () {
			$scope.list_selected_vault = false;
			$scope.creating_vault = false;
			$scope.error = false;
		};

		$scope.selectVault = function(vault){
			$scope.list_selected_vault = vault;
		};

		$scope.newVault = function(){
			$scope.creating_vault = true;
		};

		var _loginToVault = function (vault, vault_key) {
			var _vault = angular.copy(vault)
			_vault.vaultKey = angular.copy(vault_key);
			VaultService.setActiveVault(_vault);
			$location.path('/vault/'+ vault.vault_id);
		}
		
		$scope.vaultDecryptionKey = '';
		$scope.loginToVault = function (vault, vault_key) {
			$scope.error = false;
			var _vault = angular.copy(vault)
			_vault.vaultKey = angular.copy(vault_key);
			VaultService.setActiveVault(_vault);
			VaultService.getVault(vault).then(function(credentials){
				for(var i = 0; i < credentials.length; i++){
					var credential = credentials[i];
					console.log(credential);
					if(credential.hidden = true){
						try {
							var c = CredentialService.decryptCredential(credential);
							if(c.password === 'lorum ipsum'){
								console.log($scope.remember_vault_password);
								if($scope.remember_vault_password ){
									SettingsService.setSetting('defaultVaultPass', vault_key);
								}
								_loginToVault(vault, vault_key);
							}
						} catch (e){
							$scope.error = 'Incorrect vault password!'
						}
						break;
					}
				}
			})
		};

		$scope.createVault = function(vault_name, vault_key, vault_key2){
			if(vault_key != vault_key2){
				$scope.error = 'Passwords do not match';
				return;
			}
			VaultService.createVault(vault_name).then(function (vault) {
				$scope.vaults.push(vault);
				var _vault = angular.copy(vault);
				_vault.vaultKey = angular.copy(vault_key);
				VaultService.setActiveVault(_vault);
				var test_credential = CredentialService.newCredential();
				test_credential.label = 'Test key for vault '+ vault_name;
				test_credential.hidden = true;
				test_credential.vault_id = vault.vault_id;
				test_credential.password = 'lorum ipsum';
				CredentialService.createCredential(test_credential).then(function (result) {
					_loginToVault(vault, vault_key);
					//@TODO Redirect to newly created vault
				})
			});
		};
	}]);
