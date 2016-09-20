'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('ShareCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams) {
		$scope.active_vault = VaultService.getActiveVault();

		$scope.tabs = [{
			title: 'Share with users and groups',
			url: 'views/partials/forms/share_credential/basics.html',
		}, {
			title: 'Share link',
			url: 'views/partials/forms/share_credential/expire_settings.html',
			color: 'green'
		}];
		$scope.currentTab = {
			title: 'General',
			url: 'views/partials/forms/share_credential/basics.html'
		};

		$scope.onClickTab = function (tab) {
			$scope.currentTab = tab;
		};

		$scope.isActiveTab = function (tab) {
			return tab.url == $scope.currentTab.url;
		};

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
		var storedCredential = SettingsService.getSetting('share_credential');
		if (!storedCredential) {
			$location.path('/vault/' + $routeParams.vault_id);
		} else {
			$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
		}
		if ($scope.active_vault) {
			$scope.$parent.selectedVault = true;
		}
		$scope.cancel = function(){
			SettingsService.setSetting('share_credential', null);
			$location.path('/vault/' + $scope.storedCredential.vault_id);
		};


		$scope.searchUsersAndGroups = function($query){

		};

		$scope.share_settings = {
			credentialSharedWithUserAndGroup:[
				{
					userId: 'someuser',
					accessLevel: 'CAN_VIEW'
				},
				{
					userId: 'someuser',
					accessLevel: 'CAN_EDIT'
				}
			]
		};

		$scope.accessLevels = [
			{
				label: 'Can edit',
				value: 'CAN_EDIT'
			},
			{
				label: 'Can view',
				value: 'CAN_VIEW'
			}
		]



	}]);
