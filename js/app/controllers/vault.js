'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('VaultCtrl', ['$scope', 'VaultService', 'SettingsService', function ($scope, VaultService, SettingsService) {
		VaultService.getVaults().then(function (vaults) {
			$scope.vaults = vaults;
			if(SettingsService.getSetting('defaultVault') != null){
				var default_vault = SettingsService.getSetting('defaultVault');
				angular.forEach(vaults, function (vault) {
					if(vault.guid == default_vault.guid){
						$scope.default_vault = true;
						$scope.list_selected_vault = SettingsService.getSetting('defaultVault');
						return;
					}
				})
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

		$scope.createVault = function(vault_name){
			VaultService.createVault(vault_name).then(function (result) {
				console.log(result)
			})
		};
	}]);
