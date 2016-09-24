'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:RevisionCtrl
 * @description
 * # RevisionCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('RevisionCtrl', ['$scope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', function ($scope, SettingsService, VaultService, CredentialService, $location, $routeParams) {

		if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
			if (!$scope.active_vault) {
				$location.path('/')
			}
		} else {
			if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
				var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
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
		}

		$scope.cancel = function () {
			$location.path('/vault/' + $routeParams.vault_id);
			$scope.storedCredential = null;
			SettingsService.setSetting('revision_credential', null);
		}

	}]);

