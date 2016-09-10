'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('VaultCtrl', ['$scope', 'VaultService', function ($scope, VaultService) {
		VaultService.getVaults().then(function (vaults) {
			$scope.vaults = vaults;
		});


		$scope.default_vault = false;
		$scope.remember_vault_password = false;

		$scope.list_selected_vault = false;

		$scope.clearState = function () {
			$scope.list_selected_vault = false;
			$scope.creating_vault = false;
		};

		$scope.selectVault = function(vault){
			$scope.list_selected_vault = vault;
		}

		$scope.newVault = function(){
			$scope.creating_vault = true;
		}
	}]);
