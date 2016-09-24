'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:RevisionCtrl
 * @description
 * # RevisionCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('RevisionCtrl', ['$scope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$rootScope', function ($scope, SettingsService, VaultService, CredentialService, $location, $routeParams, $rootScope) {

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

		var getRevisions = function () {
			CredentialService.getRevisions($scope.storedCredential.credential_id).then(function (revisions) {
				console.log(revisions)
				$scope.revisions = revisions;
			})
		};

		if (!storedCredential) {
			CredentialService.getCredential($routeParams.credential_id).then(function(result){
				$scope.storedCredential = CredentialService.decryptCredential(angular.copy(result));
				getRevisions();
			});
		} else {
			$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
			getRevisions();
		}

		$scope.selectRevision = function(revision){
			$scope.selectedRevision = angular.copy(revision);
			$scope.selectedRevision.credential_data = CredentialService.decryptCredential(angular.copy(revision.credential_data));
			$rootScope.$emit('app_menu', true);
		};

		$scope.closeSelected = function () {
			$rootScope.$emit('app_menu', false);
			$scope.selectedRevision = false;
		};

		$scope.cancel = function () {
			$location.path('/vault/' + $routeParams.vault_id);
			$scope.storedCredential = null;
			SettingsService.setSetting('revision_credential', null);
		}

	}]);

